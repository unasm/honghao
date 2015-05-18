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
//	include '../model/honghao.php';
//	include '../model/DataBaseModel.php';
}

abstract class Getcode  extends Honghao{
	public function __construct()
	{
		//parent::__construct();
		//$this->load->model('BaseModelHttp');
		//$this->load->model('HtmlParserModel');
	}

}
?>
