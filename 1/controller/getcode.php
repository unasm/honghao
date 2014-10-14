<?php
/*************************************************************************
 * File Name :    ./getCode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 本文件主要是用来获取股票交易码的
 */

abstract class Getcode  extends Honghao{
	static $shenCode;
	static $shangCode;
	const $SHENLENGTH = 6;
	const $HULENGTH = 6;
	public function __construct()
	{
		parent::__construct();
		$this->shenCode = array();
		$this->shangCode = array();
		$this->load->model('HtmlParserModel');
		$this->load->model('BaseModelHttp');
	}
	abstract public function makecode(){}
	abstract public function getCompanyInfo(){}

	/**
	 * 检验是不是正确的，想要的深圳股票的返回页面
	 *
	 * @param $string $page		页面的html的string 
	 * @return boolen
	 **/
	public function checkPageRight($page)
	{
	}
	/**
	 * 根据传入的array 获取真正的页面
	 *
	 */
	public function createCode($prefix)
	{
		foreach($prefix as $code){
			$tmp = $code . $i;
			$page = $this->getCompanyInfo($tmp);
			if($this->checkPageRight($page)){
						
			}
		}
	}

}
?>
