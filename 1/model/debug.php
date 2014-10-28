<?php
/*************************************************************************
 * File Name :    ../model/debug.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 这里是用来调式的地方
 **/
class Debug
{
	
	private function __construct(){
				
	}
	/**
	 * 用来输出各种报错信息
	 **/
	public static function output($word , $level = E_NOTICE)
	{
		if($level === E_NOTICE || $level === 0 || $level === E_WARNING){
			var_dump($word);
			echo "<br/>";
		} elseif ($level === E_ERROR){
			var_dump($word);
			die;
		}
	}
}
