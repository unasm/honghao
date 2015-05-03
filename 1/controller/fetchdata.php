<?php
/*************************************************************************
 * @todo  获取每一次更新的时候，与众不同的值，看那些是有意义的，那些是无意义的
 * File Name :    ./controller/fetchData.php
 * Author    :    unasm
 * Mail      :    unasm@sina.cn
 ************************************************************************/
if(!class_exists('Getcode')){
	require 'getcode.php';
}
class Fetchdata extends Getcode{
	const RETRY = 3;
	//刷新页面的时候，检查的页面数
	const CheckPages = 5;
	static $cookie;
	//保存cookie
	public  function getCookie(){
		if(self::$cookie === NULL){
			$data = $this->BaseModelHttp->get(
				"http://xueqiu.com/",
				//"http://xueqiu.com/fund/quote/list.json?type=136&parent_type=13&order=desc&orderBy=percent&page=1&size=300&_=1428927429565", 
				array(), 
				10
			);
			$header = BaseModelHttp::getLastHeader();
			preg_match("/Set-Cookie\:\s*(xq_a_token=.*httpOnly)/", $header, $cookie);
			if(count($cookie) === 2){
				self::$cookie = $cookie[1];
			} else {
				var_dump($data);
				echo "adfa";
				exit("get cookie failed");
			}
		}
		return self::$cookie;
	}
	//伪造ip
	private static function _fakeIp(){
		static $ip;
		if($ip === NULL){
			$arr = array();
			for ($i = 0;$i < 4; $i++) {
				$arr[] = rand(10,230)	;
			}
			$ip = implode('.', $arr);
		}
		return $ip;
	}
	
	//伪造header头，伪造ip，防止对方拒绝
	private static function fakeHeader() {
		$header = array('CLIENT-IP:' . self::_fakeIp(),'X-FORWARDED-FOR:' . self::_fakeIp());
		return $header;
		//var_dump($header);
	}
	/**
	 * 控制调度,决定是不是要更新页面的数据
	 *
	 * 因为不可能将全部的数据都检查一遍，是不是有更新，所以采用了随机选择 2 * CheckPages 个，如果其中有一半数据更改了，就更新数据，判定雪球更新了数据
	 *
	 * @author jiamin1
	 **/
	public function index()
	{
		$this->load->model('DataBaseModel');
		$symbol = $this->DataBaseModel->exec('select distinct symbol from list ');
		$this->DataBaseModel->setTables('param');
		$maxTimes =  $this->getMaxTimes();
		$diff = 0;
		for ($start = rand(0,count($symbol) - 3 * self::CheckPages), $i = $start;$i < 2 * self::CheckPages + $start;$i++) {
			$code = $symbol[$i]['symbol'];
			$arr = $this->getPage($code);
			$pageData = $this->DataBaseModel->select('item,value',array('symbol' => $code , 'times' => $maxTimes));
			foreach($pageData as $row) {
				if (in_array($row,array('time', 'current'))) {
					//time 变化没有意义，current是必然变化的
					continue;
				}
				if (trim($arr[$row['item']]) !== trim($row['value'])) {
					//current 是注定要改变的
					echo "code diff : " . $code . "\n";
					echo $row['item']  . "\t" . $row['value']  . "\n";
					echo $arr[$row['item']] . "\n\n";
					$diff ++;
					break;
				}
			}
		}
		echo "Checking :: Now time is " . date("Y-m-d H:i:s", time()) . "\n" ;
		if($diff >= self::CheckPages || empty($symbol)){
			$time = intval(microtime(true) * 1000 );
			$this->getList();
			$this->getParam();
			$time = intval(microtime(true) * 1000) - $time;
			echo "the fetch data process time is {$time} ms\n\n";
		} else if($diff){
			echo "Snow ball is changing ,but not enough\n";
		}
	}

	/**
	 * 解析对应的数据
	 *
	 **/
	protected function getList()
	{
		$cookie = $this->getCookie();
		$data = $this->BaseModelHttp->get(
			'http://xueqiu.com/fund/quote/list.json?type=136&parent_type=13&order=desc&orderBy=percent&page=1&size=300&_=1428927' . rand(0,100000), 
			self::fakeHeader(),
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
					$data[] = array( trim($symbol), trim($key), trim($value) ,$maxTimes);	
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
		$page = $this->BaseModelHttp->get($href, self::fakeHeader(), 20, $this->getCookie());
		$this->HtmlParserModel->parseStr($page);
		preg_match("/SNB.data.quote\s*\=\s*(\{[^}]*\})/", $page, $res);
		if(count($res) === 2){
			$ts = json_decode($res[1], true);
			if($ts && is_array($ts)){
				return $ts;	
			}
		}
		echo __LINE__ . "没有获取想要的信息\n";
		var_dump($page);
		exit;
	}

	/**
	 * 获取全部的详情信息，
	 *
	 **/
	protected function getParam()
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
				$data[] = array(trim($code['symbol']), trim($key), trim($value), $times);
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

	/**
	 * 获取所有的基金当前价格
	 *
	 * @author jiamin1
	 **/
	public function getCurrent($code = "SZ161714")
	{
		for ($i = 0;$i < self::RETRY; $i++) {
			$data = $this->BaseModelHttp->get(
				'http://xueqiu.com/stock/forchart/stocklist.json?symbol=' . $code . '&period=2d&_=14'  . rand(0, 100000),
				array(), 
				20,
				self::getCookie()
			);
			$data && $data =  json_decode($data , true);
			
			if ($data === false || !isset($data['chartlist']) || !is_array($data['chartlist'])) {
				continue;
			}
			$list = $data['chartlist'];
			$time = array();
			foreach ($list as $key => $each){
				//更新成时间戳，方便处理
				$tmp = strtotime($each['time']) ;
				$list[$key]['time'] = $tmp;
				$time[] = $tmp;
			}
			array_multisort($time, SORT_NUMERIC, $list);
			$res = array();
			for ($i = 0, $len = count($list);$i < $len; $i++) {
				if($i === 0 || $list[$i]['volume'] != '0' || $list[$i]['current'] != $list[$i - 1]['current']){
					$res[] = $list[$i];
				}
			}
			return $res;
		}
		return array();
	}

	/**
	 * 更新目前的市场价格
	 *
	 * @return bool
	 * @author jiamin1
	 **/
	public function freshPrice()
	{
		$this->load->model("DataBaseModel")	;
		$this->DataBaseModel->setTables('param');
		$sys = $this->DataBaseModel->exec('select distinct symbol from list ');
		$maxTimes =  $this->getMaxTimes();//这里应该考虑到锁的情况
		srand((int)microtime());
		$flag = rand(0,100000);
		echo "{$flag} Checking :: Now time is " . date("Y-m-d H:i:s", time()) . "\n" ;
		foreach ($sys as $code) {
			$arr = $this->getCurrent($code['symbol']);
			if (is_array($arr) && count($arr)) {
				$current = trim($arr[count($arr) - 1]['current']);
				$flag = $this->DataBaseModel->update(
					array('value' => $current),
					array('times' => $maxTimes, 'item' => 'current', 'symbol' => $code['symbol'])
				);
			}
		}
		echo "{$flag} ending :: Now time is " . date("Y-m-d H:i:s", time()) . "\n\n" ;
	}

	/**
	 * 设置当前最合适的times
	 *
	 * @return array
	 * @author jiamin1
	 **/
	protected function _setTimes()
	{
		$time = time();
		$time = strtotime("2013-03-12 11:00:00");
		var_dump($time);
	}
}
