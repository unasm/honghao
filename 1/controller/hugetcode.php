<?php
/*************************************************************************
 * File Name :    ./hugetcode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 获取沪市的交易信息
 **/
class Hugetcode extends Getcode
{
	const CODELENGTH = 5;	
	function __construct()
	{}
	/**
	 * 生成沪市的上市公司代码
	 */
	public function makecode()
	{		
		die("make code");
		//沪市股票的开头,创业版，中小版，配股，新股,沪市A股是600或者是601，B股,配股
		$prefix = array('300' , '002' , '700' , '730' , '600' , '601' , '900','580');
		for($i = 0; $i < 10000;$i++){
			$this->createCode($prefix);
		}
	}
	function index(){
		$this->makecode();
	}
}
