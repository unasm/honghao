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
	static $times = -1;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BaseModelHttp');
		$this->load->model('HtmlParserModel');
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
}
?>
