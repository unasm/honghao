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
	function __construct()
	{
		parent::__construct();
		$this->load->model('HtmlParserModel')	;
		$this->load->model('DataBaseModel');
	}

	/**
	 * 显示页面
	 *
	 **/
	public function index()
	{
		//setcookie("user","jiamin", time() + 3600)		;
		setcookie("user","jiamin")		;

		$list = $this->selectViewList();
		include PATH_ROOT  . 'view/index.html';
	}
	/**
	 * 处理雪球所需要的数据
	 *
	 * @return assoc array
	 **/
	protected function selectViewList()
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
}
