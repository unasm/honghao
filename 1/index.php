<?php
define("PATH_ROOT" , rtrim(dirname(__FILE__) , "/") . "/");
define('BasePath' , rtrim(dirname(__FILE__) , '/') . "/" );

require PATH_ROOT . 'model/common.php';
//require PATH_ROOT . 'model/view.php';
require PATH_ROOT . 'model/core.php';

require PATH_ROOT . 'model/error.php';
require PATH_ROOT . 'model/honghao.php';
//这里直接在route中调用了目标地址,直接跳转，或许这样不好
require PATH_ROOT . 'model/route.php';
 // 魔术函数，自动添加寻找对应的模块
function autoloader($className)
{
	echo $className;
	if(preg_match('Model' , $className)){
		include BasePath . 'model/'	 . $className . '.class.php';
	}	
}
if(!function_exists('get_instance')){
	function &get_instance()
	{
		return Honghao::$instance;
	}
}
$route = new Route;
include $route->path . strtolower($route->class) . '.php';
$route->class = ucwords($route->class);
$tmp = new $route->class();
$tmp->{$route->function}();
// @todo 这里的code的方式不合适,应该通过get的方式获取

