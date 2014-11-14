<?php
echo "sdf";
return;
if(isset($_GET['signature']) && isset($_GET['timestamp']) && isset($_GET['nonce'])){
	require PATH_ROOT . 'model/wx.php';
	return;
}
define("PATH_ROOT" , rtrim(dirname(__FILE__) , "/") . "/");
define('BasePath' , rtrim(dirname(__FILE__) , '/') . "/" );

require PATH_ROOT . 'model/common.php';
//require PATH_ROOT . 'model/view.php';
//require PATH_ROOT . 'model/core.php';

require PATH_ROOT . 'model/error.php';
require PATH_ROOT . 'model/debug.php';
require PATH_ROOT . 'model/honghao.php';
//这里直接在route中调用了目标地址,直接跳转，或许这样不好
require PATH_ROOT . 'model/route.php';

if(!function_exists('get_instance')){
	function &get_instance()
	{
		return Honghao::$instance;
	}
}

if(!function_exists('output')){
	/**
	 * 输入到微信端
	 */
	function output($value){
		if(is_array($value)){
			//var_dump($value);	
			foreach($value as $idx => $data){
				echo $idx . "=>" . "<br/>";
				var_dump($data);
				echo "<br/>";
				echo "<br/>";
			}
		} else {
			echo $value . "<br/>";	
		}
	}
}
set_error_handler('myerror' , E_ALL);
$route = new Route;
include $route->path . strtolower($route->class) . '.php';
$route->class = ucwords($route->class);
$tmp = new $route->class;
$tmp->{$route->function}();
try{
	//虽然无用，但是依旧保留的一场捕获

} catch (Exception $e){
	echo "error Code is :"  , $e->getCode() . "<br/>";
	echo $e->getMessage() . "<br/>";
}
// @todo 这里的code的方式不合适,应该通过get的方式获取

