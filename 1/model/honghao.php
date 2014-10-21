<?php
/*************************************************************************
 * File Name :    ./model/honghao.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 父函数
 **/
class Honghao
{
	
	static $instance;
	static $flag;
	function __construct()
	{
		
		$this->load = new Loader ;
		self::$instance = &$this;
	}
	/**
	 * 为以后访问主程序准备接口
	 */
	protected function setInstance($instance)
	{
		//self::$instance = $instance;
	}
}
