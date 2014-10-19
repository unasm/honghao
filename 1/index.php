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
//错误处理
function myerror($level, $message){
	$delimite = "<br/>";
	if(isset($_SERVER['argc'])){
		$delimite = "\n";
	}
	$info = '错误号 : ' . $level . $delimite;
	$info .= '错误信息 : ' . $message .$delimite;	
	$info .= '发生时间 : '.date('Y-m-d H:i:s') . $delimite . $delimite;
	echo $info;
}
set_error_handler('myerror' , E_ALL);
$route = new Route;
include $route->path . strtolower($route->class) . '.php';
$route->class = ucwords($route->class);
try{
	//虽然无用，但是依旧保留的一场捕获
	$tmp = new $route->class();
	$tmp->{$route->function}();
} catch (Exception $e){
	echo "error Code is :"  , $e->getCode() . "<br/>";
	echo $e->getMessage() . "<br/>";
}
// @todo 这里的code的方式不合适,应该通过get的方式获取

