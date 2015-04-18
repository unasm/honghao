<?php
/*************************************************************************
 * File Name :    ./controller/fetchData.php
 * Author    :    unasm
 * Mail      :    unasm@sina.cn
 ************************************************************************/
if(!class_exists('Getcode')){
	require 'getcode.php';
}
class FetchData extends Getcode{
	const RETRY = 3;
	//刷新页面的时候，检查的页面数
	const CheckPages = 3;
	static $cookie;
	//保存cookie
	public  function getCookie(){
		if(self::$cookie === NULL){
			$data = $this->BaseModelHttp->get(
				"http://xueqiu.com/",
				//"http://xueqiu.com/fund/quote/list.json?type=136&parent_type=13&order=desc&orderBy=percent&page=1&size=300&_=1428927429565", 
				array(), 
				20
			);
			$header = BaseModelHttp::getLastHeader();
			preg_match("/Set-Cookie\:\s*(xq_a_token=.*httpOnly)/", $header, $cookie);
			if(count($cookie) === 2){
				self::$cookie = $cookie[1];
			} else {
				exit("get cookie failed");
			}
		}
		return self::$cookie;
	}

	/**
	 * 控制调度,决定是不是要更新页面的数据
	 *
	 * @author jiamin1
	 **/
	public function index()
	{
		$this->load->model('DataBaseModel');
		$symbol = $this->DataBaseModel->exec('select distinct symbol from list ');
		$this->DataBaseModel->setTables('param');
		$cnt = 2 * self::CheckPages;
		$maxTimes =  $this->getMaxTimes();
		$diff = 0;
		foreach ($symbol as $code) {
			$arr = $this->getPage($code['symbol']);
			$pageData = $this->DataBaseModel->select('item,value',array('symbol' => $code , 'times' => $maxTimes));
			foreach($pageData as $row) {
				if ($arr[$row['item']] !== $row['value']) {
					echo "diff \n";
					var_dump($arr[$row['item']]);
					var_dump($row['value']);
					$diff ++;
					break;
				}
			}
			$cnt --;
			if($cnt === 0){
				break;	
			}
		}
		if($diff >= self::CheckPages){
			$this->getList();
			$this->getParam();
		}
	}

	/**
	 * 解析对应的数据
	 *
	 **/
	public function getList()
	{
		$cookie = $this->getCookie();
		$data = $this->BaseModelHttp->get(
			"http://xueqiu.com/fund/quote/list.json?type=136&parent_type=13&order=desc&orderBy=percent&page=1&size=300&_=1428927429565", 
			array(), 
			20,
			$cookie
		);
		$data = json_decode($data, true);
		$maxTimes = $this->getMaxTimes() + 1;
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
					$data[] = array( $symbol, $key, $value ,$maxTimes);	
				}
				$items = array('symbol', 'item', 'value', 'times');
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
	 * 获取详情页的信息
	 *
	 * @return void
	 * @author Me
	 **/
	private function getPage($code = 'SZ163112')
	{
		$href = "http://xueqiu.com/S/" . $code ;
		$page = $this->BaseModelHttp->get($href,array(), 20, $this->getCookie());
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
	public function getParam()
	{
		//获取想要搜索的股票的代码
		$symbol = $this->DataBaseModel->exec('select distinct symbol from list ');
		$this->DataBaseModel->setTables('param');
		$times = $this->getMaxTimes() + 1;
		foreach($symbol as $code){
			var_dump($code['symbol']);
			$arr = $this->getPage($code['symbol']);
			if (!isset($arr['symbol']) || $arr['symbol'] !== $code['symbol']) {
				exit(__LINE__ . "行没有想要的返回值");
			}
			unset($arr['symbol']);
			$data = array();
			foreach ($arr as $key => $value) {
				$data[] = array($code['symbol'], trim($key), trim($value), $times);
			}
			$items = array('symbol', 'item', 'value', 'times');
			for($i = 0;$i < self::RETRY;$i++){
				$rs = $this->DataBaseModel->insert($items, $data);
				if ($rs) {
					break;
				} else {
					sleep(10);
				}
			}
		}
	}
}
