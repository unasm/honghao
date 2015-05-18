<?php
/*************************************************************************
 * File Name :    ./model/XueqiuModel.php
 * Author    :    unasm
 * Mail      :    unasm@sina.cn
 ************************************************************************/

/**
 * 雪球本身的model
 **/
class XueqiuModel extends Honghao
{
	static $times = -1;
	function __construct(){
		parent::__construct();
		//$this->load->model('HtmlParserModel')	;
		$this->load->model('DataBaseModel');
	}

	/**
	 * 处理雪球所需要的数据
	 *
	 * @return assoc array
	 **/
	public function selectViewList()
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
		foreach($dets as $key => $code){
			if ($code['pe_lyr'] == 0) {
				$dets[$key]['overflow'] = 0;
			} else {
				$dets[$key]['overflow'] = round( ($code['current'] - $code['pe_lyr']	) / $code['pe_lyr'] * 100, 3);
			}
		}
		$this->sortByOrderKey($dets, 'overflow');
		return $dets;
	}
	/**
	 * 根据指定的key进行排序
	 *
	 * @author jiamin1
	 **/
	public function sortByOrderKey(&$arr , $key)
	{
		$keys = array();
		foreach($arr as $code){
			$keys[] = $code[$key];
		}
		array_multisort($keys, SORT_DESC, SORT_NUMERIC, $arr);
	}
	//获取雪球的list
	protected function selectList(){
		$maxTimes = $this->getMaxTimes();
		$items = implode('\',\'' , array('percent','name', 'subscription_status', 'redemption_status') );
		$sql = "select `symbol`, `value`, `item` from list where item in('{$items}') && times = {$maxTimes}";
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
		$maxTimes = $this->getMaxTimes();
		$items = implode("','", array('volume', 'current', 'pe_lyr'));
		$sql = "select symbol,item,value from param where item in('{$items}') && `symbol` in('{$symStr}') && times = {$maxTimes}";
		$dets = $this->DataBaseModel->exec($sql);
		return 	$this->DataBaseModel->format($dets, 'symbol', 'item' , 'value');
	}

	/**
	 * 获取当前数据库表中最新的一批数据
	 *
	 * @return void
	 **/
	public function getMaxTimes()
	{
		if(self::$times === -1){
			$res = $this->DataBaseModel->exec('select max(times) as times from list');
			self::$times = $res[0]['times'];
		}
		return self::$times;	
	}

	/**
	 * 获取最新的报价
	 *
	 * @return array
	 * @author unasm
	 **/
	public function getCurrentList()
	{
		$list = $this->selectList();
		if(empty($list)){
			return array();
		}
		$times = $this->getMaxTimes();
		$str = implode('\',\'', array_keys($list)) ;
		$res = $this->DataBaseModel->exec("select * from param where symbol in('{$str}')  && item = 'current'  && times= '{$times}' " );
		return $this->DataBaseModel->format($res, 'symbol', 'item' , 'value');
	}
}
