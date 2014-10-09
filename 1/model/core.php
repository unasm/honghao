<?php
/*************************************************************************
 * File Name :    ../core/core.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 这里实现系统的资源调度
 **/
class FrameWork
{
	static $load;
	function __construct()
	{
		$this->load = &Loader::instance() ;
		$this->load->library("sdfa" , "sdf");
	}
}
?>
