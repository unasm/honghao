<?php

define('BasePath' , rtrim(dirname(__FILE__) , '/') . "/" );
/**
 * 魔术函数，自动添加寻找对应的模块
 */
function autoloader($className)
{
	if(preg_match('Model' , $className)){
		include BasePath . 'model/'	 . $className . '.class.php';
	}	
}
//spl_autoload_register('autoloader');
?>
