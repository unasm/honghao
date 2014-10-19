<?php
/*************************************************************************
 * File Name :    ./getCode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 本文件主要是用来获取股票交易码的
 */
if(isset($_SERVER['argc'])){
	include '../model/honghao.php';
	include '../model/DataBaseModel.php';
}

abstract class Getcode  extends Honghao{
	static $shenCode;
	static $shangCode;
	public function __construct()
	{
		parent::__construct();
		$this->shenCode = array();
		$this->shangCode = array();
		$this->load->config('db');
		$this->load->model('DataBaseModel');
		$this->load->model('HtmlParserModel');
		$this->load->model('BaseModelHttp');
	}
	public function makecode(){}
	public function getCompanyInfo(){}

	/**
	 * 检验是不是正确的，想要的深圳股票的返回页面
	 *
	 * @param $string $page		页面的html的string 
	 * @return boolen
	 **/
	public function checkPageRight($page)
	{
		return true;
	}
	/**
	 * 根据传入的array 获取真正的页面
	 *
	 */
	public function createCode($prefix , $pos)
	{
		$res = array();
		foreach($prefix as $code){
			$tmp = $code . $pos;
			$page = $this->getCompanyInfo($tmp);
			if($this->checkPageRight($page)){
				$res[] = $tmp;
			}
		}
		return $res;
	}

}
?>
