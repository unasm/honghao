<?php
/*************************************************************************
 * File Name :    ./test.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 
 **/
class base
{
	static $data;
	var $data2;
	function __construct($get)
	{
		self::$data = $get;
		$this->data2 = $get;
	}
	function get(){
		echo self::$data . "\n";
		echo $this->data2 . "\n";
	}
}
$first = new base("data1");
$sec = new base("test");
$first->get();
$sec->get();
