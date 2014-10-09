<?php
/*************************************************************************
 * 这里处理的是路由分发和过滤
 * File Name :    ./route.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
//var_dump($_SERVER);
$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];


$urlArr = parse_url($url);
$urlArr['path'] = trim($urlArr['path'] , "/");
if(!$urlArr['path']){
	$urlArr['path'] = 'home';
}

/**
 * 检查路由是否符合描述规则
 */
function checkRoute(&$urlArr){

	//value中是对应的request_uri的正则格式
	$route['home'] = "/^((home)?\?code\=[\w\d]+)?$/";
	$route['user'] = "/^(user\?uid\=[\d]+)?$/";
	$route['user/index'] = "/^user\/index\?uid\=[\d]+$/";
	$route['api/reply/send'] = "/^api\/reply\/send\?id\=[\d]+$/";
	$route['api/favor/index'] = "/^api\/favor\/index\?status\=[\w]{3,10}&id=[\d]+$/";
	$route['api/upload/index'] = "//";
	//$route['write'] = "";
	$request = ltrim($_SERVER['REQUEST_URI'] , '/');
	if(preg_match('/^\/core/' , $urlArr['path'])){
		exit("you are not permit to access");
	}
	//echo $request .'<br/>';
	//路径中不能包含非英文字符
	if($urlArr['path'] && !preg_match("/^[\/\w]+$/" , $urlArr['path'])){
		exit("route is invalid");
	}
	if(!array_key_exists($urlArr['path'],$route) ||  !preg_match($route[$urlArr['path']] , $request)){
		error("the request is invalid");
	}
}
/**
 * 实例化对应的函数
 * @param string $path  文件的路由
 */
function instancePath($path){
	$cnt = 2;
	$routePath = array();
	$path = '/' . $path;
	for($i = strlen($path) -1 ;$i >= 0;$i--){
		if($path[$i] === '/' && $cnt > 0 && $tmp){
			$tmp = strrev($tmp)	;
			$routePath[$cnt] = $tmp;
			$cnt --;
			if(($cnt === 0) && ($i > 0)){
				$routePath[$cnt] = ltrim(substr($path, 0,$i) , "/");
				break;
			}
			$tmp = '';
		}else{
			$tmp .= $path[$i];
		}
	}
	$len = count($routePath);
	$function = "";
	$class = "";
	$path = PATH_ROOT . "server/";
	if($len === 0){
		$class = "home"	;
		$function = 'index';
	}elseif($len === 1){
		$function = 'index';
		$class = $routePath[2];
	}else if($len === 2){
		$function = $routePath[2];
		$class = $routePath[1];
	}else{
		$function = $routePath[2];
		$class = $routePath[1];
		$path .= $routePath[0] . "/";
	}
	if(file_exists($path . $class . '.php')){
		require $path . $class .".php";
		$objClass = new $class;
		$objClass->$function();
	}else{
		show_404();
	}

}
checkRoute($urlArr);
instancePath($urlArr['path']);

?>
