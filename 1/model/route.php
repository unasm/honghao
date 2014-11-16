<?php
/*************************************************************************
 * 这里处理的是路由分发和过滤
 * File Name :    ./route.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
//var_dump($_SERVER);
/**
 * 这里是进行的路由解析
 **/
class Route
{
	public $class , $function , $path;
	function __construct()
	{
		$urlArr = array();
		if(isset($_SERVER['argv'])){
			if($_SERVER['argc'] > 1){
				$urlArr['path'] = $_SERVER['argv'][1];
			}else{
				$urlArr['path'] = '';
			}
		}else{
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$urlArr = parse_url($url);
			$urlArr['path'] = trim($urlArr['path'] , "/");	
		}
		if(!$urlArr['path'] || empty($urlArr)){
			$urlArr['path'] = 'home';
		}
		//$this->checkRoute($urlArr);
		$this->instancePath($urlArr['path']);
	}
	/**
	 * 检查路由是否符合描述规则
	 * @todo $request 有待改进
	 */
	function checkRoute(&$urlArr){
		//value中是对应的request_uri的正则格式
		include PATH_ROOT . 'config/route.php';
		$request = '';
		if(isset($_SERVER['REQUEST_URI'])){
			$request = ltrim($_SERVER['REQUEST_URI'] , '/');
		}
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
		$tmp = '';
		for($i = strlen($path) - 1 ;$i >= 0;$i--){
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
		//去除index.php
		if(array_key_exists('0' , $routePath) && $routePath[0] === 'index.php' ){
			array_pop($routePath);
			$len --;
		}
		$function = '';
		$class = '';
		$path = PATH_ROOT . "controller/";
		if($len === 0){
			$class = 'home'	;
			$function = 'index';
		} elseif ($len === 1){
			$function = 'index';
			$class = $routePath[2];
		} elseif ($len === 2){
			$function = $routePath[2];
			$class = $routePath[1];
		} elseif ($len === 3) {
			$function = $routePath[2];
			$class = $routePath[1];
			$path .= $routePath[0] . "/";
		} else {
			error("错误");
		}
		if(file_exists($path . $class . '.php')){
			$this->path = $path;
			$this->class = $class;
			$this->function = $function;
		}else{
			die("没有具体路由");
			show_404();
		}
	}
}

?>
