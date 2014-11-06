<?php
/*************************************************************************
 * File Name :    ./hugetcode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 获取沪市的交易信息
 **/
/*
if(isset($_SERVER['argc'])){
}
*/
if(!class_exists('Getcode')){
	require 'getcode.php';
}
define("DEBUG" , true);
class Hugetcode extends Getcode
{
	function __construct()
	{
		$this->load->model('BaseModelHttp');
	}
	/**
	 * 生成沪市的上市公司代码
	 */
	public function makecode()
	{		
		//沪市股票的开头,创业版，中小版，配股，新股,沪市A股是600或者是601，B股,配股
		//$prefix = array('300' , '002' , '700' , '730' , '600' , '601' , '900','580');
		//echo $this->getTime() . "\n";
		$flagTime = strtotime('1999-0-0');
		$prefix = "60";
		for($i = 0; $i <= 9999 ;$i++){
			$len = 4 - strlen($i);
			while($len --){
				$i = '0' . $i;
			}
			$i = $prefix . $i;
			//$cnt = 0;//  $end  = false; $flagTime = false;
			for($cnt = 0;$cnt < 16;){
				$start = $this->getTime('-' , $cnt);
				$cnt +=3;
				$end = $this->getTime('-' , $cnt);					
				/*
				$url = "http://www.sse.com.cn/assortment/stock/list/stockdetails/announcement/index.shtml?COMPANY_CODE=600300" .
				"&startDate=2002-09-05&endDate=2004-09-22&" . 
				"productId=600300&startDate=2014-08-06&" . 
				"endDate=2014-11-06&reportType=ALL&reportType2=%E5%AE%9A%E6%9C%9F%E5%85%AC%E5%91%8A&reportType=ALL&moreConditions=true";
				 */
				$url = "http://www.sse.com.cn/assortment/stock/list/stockdetails/announcement/index.shtml?COMPANY_CODE=". $i .
				"&startDate={$start}&endDate={$end}&" . 
				"productId=600300&startDate=2014-08-06&" . 
				"endDate=2014-11-06&reportType=ALL&reportType2=%E5%AE%9A%E6%9C%9F%E5%85%AC%E5%91%8A&reportType=ALL&moreConditions=true";
				$page = $this->BaseModelHttp->get($url ,array() ,20);
				if((int)strtotime($end) < (int)$flagTime){
					break;
				}
			}

			//$this->createCode($prefix ,$i);
		}
	}
	function index(){
		$this->makecode();
	}
}
$test = new Hugetcode();
$test->makecode();
