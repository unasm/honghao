<?php
/*
function test(){
	if(!array_key_exists('HTTP_RAW_POST_DATA' , $GLOBALS)){
		return false;	
	}
	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

	//extract post data
	if (!empty($postStr)){
		libxml_disable_entity_loader(true);
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$content = "您发送的是文本内容，内容为 : " . $postObj->Content;
		$textTpl = "<xml>
		<ToUserName><![CDATA[{$postObj->FromUserName}]]></ToUserName>
		<FromUserName><![CDATA[{$postObj->ToUserName}]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		<FuncFlag>%d</FuncFlag>
		</xml>";             
		echo sprintf($textTpl, time(), $content, '0');
		//echo $textTpl;
	}
}
test();
return;
 */
define("PATH_ROOT" , rtrim(dirname(__FILE__) , "/") . "/");
define('BasePath' , rtrim(dirname(__FILE__) , '/') . "/" );

//如果是教研的话，就到此为止吧
if(isset($_GET['signature']) && isset($_GET['timestamp']) && isset($_GET['nonce'])){
	require PATH_ROOT . 'model/wx.php';
	$wechatObj = new wx();
	$wechatObj->valid();
	return;
}


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
	function output($value ,$type = 'wx'){
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

