<?php
/*************************************************************************
 * File Name :    xueqiu.php
 * Author    :    unasm
 * Mail      :    unasm@sina.cn
 ************************************************************************/
/**
 * 获取雪球的信息
 **/
if(!class_exists('Getcode')){
	require 'getcode.php';
}
define("DEBUG", false);
class Home extends Getcode
{
	const RETRY = 3;
	function __construct()
	{
		parent::__construct();
		$this->load->model('HtmlParserModel')	;
		$this->load->model('DataBaseModel');
	}
	public static function getCookie(){
		return "xq_a_token=cd4627d424e2788da0f6befb9e3d71437e07828a; xq_r_token=f042a845683871779b0be87f75686be3d4293b85; __utmt=1; __utma=1.2028982786.1427082007.1428927400.1429028807.4; __utmb=1.1.10.1429028807; __utmc=1; __utmz=1.1427082007.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); Hm_lvt_1db88642e346389874251b5a1eded6e3=1427082007,1428827605,1428855668; Hm_lpvt_1db88642e346389874251b5a1eded6e3=1429028807";
	}

	/**
	 * 解析对应的数据
	 *
	 **/
	public function getList()
	{
		$cookie = self::getCookie();
		//public static function get($req, array $header = array(), $timeout = self::DAGGER_HTTP_TIMEOUT, $cookie = '', $redo = self::DAGGER_HTTP_REDO, $maxredirect = self::DAGGER_HTTP_MAXREDIRECT) {
		$data = $this->BaseModelHttp->get(
			"http://xueqiu.com/fund/quote/list.json?type=136&parent_type=13&order=desc&orderBy=percent&page=1&size=300&_=1428927429565", 
			array(), 
			20,
			$cookie
		);
		//file_put_contents('/Users/tianyi/Desktop/xueqiu.html',$data, true);
		$data = json_decode($data, true);

		if(isset($data['stocks'])){
			$this->DataBaseModel->setTables('list');
			foreach($data['stocks'] as $stock){
				//echo count($stock) . "\n";
				if(count($stock) !== 19){
					echo __LINE__ .  "是个很奇怪的stock";
					var_dump($stock);
					exit;
				}
				$symbol = $stock['symbol'];
				unset($stock['symbol']);
				$data = array();
				foreach($stock as $key => $value){
					$data[] = array( $symbol, $key, $value );	
				}
				$items = array('symbol', 'item', 'value');
				for($j = 0;$j < self::RETRY;$j++){
					$rs = $this->DataBaseModel->insert($items, $data);
					if($rs){
						break;	
					} else {
						sleep(1);
					}
				}
			}
		} else {
			echo "no stock data\n";
		}

		//$page = $this->BaseModelHttp->get("http://xueqiu.com/hq#exchange=CN&plate=5_2_6&firstName=5&secondName=5_2&fundtype=136&pfundtype=13&page=2", array(), 20, $cookie);
	}

	/**
	 * 获取当前的交易价格
	 *
	 **/
	public function getCurrent($page)
	{
		$this->HtmlParserModel->parseStr($page);
		$stocks = $this->HtmlParserModel->find('#currentQuote');
		$value = $stocks[0]->value;
		preg_match("/data\-current\=\s*\"(\d+\.\d*)\"\s*/", $value, $res);
		if(is_numeric($res[1])){
			return $res[1];
		}
		echo "it is not current value\n";
		var_dump($value);
		exit;
		return false;
		//var_dump($stocks[0]->value);
		//var_dump($stocks->value);
	}

	/**
	 * 获取详情页的信息
	 *
	 * @return void
	 * @author Me
	 **/
	private function getPage($code = 'SZ163112')
	{
		$href = "http://xueqiu.com/S/" . $code ;
		$page = $this->BaseModelHttp->get($href,array(), 20,self::getCookie());
		$this->HtmlParserModel->parseStr($page);
		preg_match("/SNB.data.quote\s*\=\s*(\{[^}]*\})/", $page, $res);
		$ts = json_decode($res[1], true);
		if($ts && is_array($ts)){
			return $ts;	
		}
		echo __LINE__ . "没有获取想要的信息";
		var_dump($page);
		exit;
	}
	/**
	 * 获取全部的详情信息，
	 *
	 **/
	public function getAllDet()
	{
		$symbol = $this->DataBaseModel->exec('select distinct symbol from list ');
		$this->DataBaseModel->setTables('param');
		foreach($symbol as $code){
			var_dump($code['symbol']);
			$arr = $this->getPage($code['symbol']);
			if (!isset($arr['symbol']) || $arr['symbol'] !== $code['symbol']) {
				exit(__LINE__ . "行没有想要的返回值");
			}
			unset($arr['symbol']);
			$data = array();
			foreach ($arr as $key => $value) {
				$data[] = array($code['symbol'], $key, $value);
			}
			$items = array('symbol', 'item', 'value');
			for($i = 0;$i < self::RETRY;$i++){
				$rs = $this->DataBaseModel->insert($items, $data);
				if($rs){
					break;
				} else {
					sleep(1);
				}
			}
		}
	}

	/**
	 * 显示页面
	 *
	 **/
	public function index()
	{
		//$this->load->model("Xueqiu");
		$list = $this->selectViewList();
		include PATH_ROOT  . 'view/index.html';
	}
	/**
	 * 处理雪球所需要的数据
	 *
	 * @return assoc array
	 **/
	private function selectViewList()
	{
		$list = $this->selectList();
		$symbol = array();
		foreach($list as $sym){
			$symbol[]	 = $sym['symbol'];
		}
	
		$dets = $this->selectParam($symbol)	;
		foreach($dets as $k => $v){
			$dets[$k] = array_merge($dets[$k], $list[$k]);
		}
		$overflow = array();
		foreach($dets as $key => $code){
			$tmp = round( ($code['current'] - $code['pe_lyr']	) / $code['pe_lyr'] * 100, 3);
			$overflow[] = $tmp;
			$dets[$key]['overflow'] = $tmp;
		}
		array_multisort($overflow, SORT_DESC, SORT_NUMERIC,$dets);
		return $dets;
	}
	
	//获取雪球的list
	protected function selectList(){
		$items = implode('\',\'' , array('percent','name', 'subscription_status', 'redemption_status') );
		$sql = "select `symbol`, `value`, `item` from list where item in('{$items}')";
		$datas = $this->DataBaseModel->exec($sql);
		return $this->DataBaseModel->format($datas, 'symbol', 'item' , 'value');
	}
	/**
	 * 获取参数
	 *
	 * @param	array $symbol	股票的代码数组
	 */
	protected function selectParam($symbol){
		$symStr = implode("','", $symbol);
		$items = implode("','", array('volume', 'current', 'pe_lyr'));
		$sql = "select symbol,item,value from param where item in('{$items}') && `symbol` in('{$symStr}')";
		$dets = $this->DataBaseModel->exec($sql);
		return 	$this->DataBaseModel->format($dets, 'symbol', 'item' , 'value');
	}
}
