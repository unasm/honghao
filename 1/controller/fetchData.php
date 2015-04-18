<?php
/*************************************************************************
 * File Name :    ./controller/fetchData.php
 * Author    :    unasm
 * Mail      :    unasm@sina.cn
 ************************************************************************/
class FetchData extends Getcode{
	const RETRY = 3;
	//刷新页面的时候，检查的页面数
	const CheckPages = 3;
	//保存cookie
	public static function getCookie(){
		return "xq_a_token=cd4627d424e2788da0f6befb9e3d71437e07828a; xq_r_token=f042a845683871779b0be87f75686be3d4293b85; __utmt=1; __utma=1.2028982786.1427082007.1428927400.1429028807.4; __utmb=1.1.10.1429028807; __utmc=1; __utmz=1.1427082007.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); Hm_lvt_1db88642e346389874251b5a1eded6e3=1427082007,1428827605,1428855668; Hm_lpvt_1db88642e346389874251b5a1eded6e3=1429028807";
		$data = $this->BaseModelHttp->get(
			"http://xueqiu.com/fund/quote/list.json?type=136&parent_type=13&order=desc&orderBy=percent&page=1&size=300&_=1428927429565", 
			array(), 
			20,
			$cookie
		);
	}

	/**
	 * 控制调度,决定是不是要更新页面的数据
	 *
	 * @author jiamin1
	 **/
	public function index()
	{
		$symbol = $this->DataBaseModel->exec('select distinct symbol from list ');
		$this->DataBaseModel->setTables('param');
		$cnt = 2 * self::CheckPages;
		foreach ($symbol as $code) {
			$arr = $this->getPage($code);
			$pageData = $this->DataBaseModel->select('item,value',array('symbol' => $code , 'times' => $maxTimes));
			//$pageData = $this->DataBaseModel->format($pageData, 'symbol', 'item', 'value');
			var_dump($pageData);
			var_dump($arr);
			die;
			foreach($pageData as $row) {
				if ($arr[$row['item']] !== $row['value']) {
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
		$cookie = self::getCookie();
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
		for($i = 0; $i < self::RETRY; $i++){
			$page = $this->BaseModelHttp->get($href,array(), 20,self::getCookie());
			$header = BaseModelHttp::getLastHeader();
			die;
		}
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
