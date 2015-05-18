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
		$this->load->model('XueqiuModel');
	}

	/**
	 * 显示页面
	 *
	 **/
	public function index()
	{
		$list = $this->XueqiuModel->selectViewList();
		include PATH_ROOT  . 'view/index.html';
	}

	public function current() {
		$list = $this->XueqiuModel->getCurrentList();
		echo json_encode($list);
	}

}
