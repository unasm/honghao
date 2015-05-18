<?php
/*************************************************************************
 * File Name :    ../core/common.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
function siteUrl($url){
	return "http://test.tianyi.com/" . trim($url, '/');
}
if(!function_exists('show_404')){
	//显示网页错误
	function show_404()
	{}
}

if(!class_exists('Loader')){
	/**
	 * 这里是调用系统资源的，如lib，如view，如model
	 **/
	class Loader
	{
		//单例模式	
		//static $obj;
		var $is_loaded;
		function __construct(){
			$this->is_loaded = array();
		}
		final public  static function &instance()
		{
			//单例模式
		}
		public function __call($funcName , $files ){
			/*
			echo $funcName  . "<br/>";
			var_dump($files);
			echo "<br/>";
			 */
			$instance = &get_instance();
			if($funcName === 'config'){
				foreach($files as $class){
					if(array_key_exists($funcName , $this->is_loaded) && array_key_exists($class , $this->is_loaded[$funcName])){
						return;	
					}
					include PATH_ROOT .$funcName . '/' .  $class . '.php';
					$this->is_loaded[$funcName][$class] = true;
					if(!isset($instance->config) || empty($instance->config)){
						$instance->config = array();
					}
					$instance->config = array_merge($instance->config , $config);
				}
			}else{
				$type = array('model' , 'library' , 'view' );
				if(in_array($funcName , $type)){
					foreach ($files as $class) {
						if(array_key_exists($funcName , $this->is_loaded) 
							&& array_key_exists($class , $this->is_loaded[$funcName]) 
							&& $this->is_loaded[$funcName][$class]
						){
							return;	
						}
						//如果存在.的话，证明是有后缀名的
						if(strpos($class, '.')){
							include PATH_ROOT .$funcName . '/' .  $class;
						} else {
							include PATH_ROOT .$funcName . '/' .  $class . '.php';
							//echo $class . "<br/>";
							$instance->$class = new $class();
						}
						$this->is_loaded[$funcName][$class] = true;
							//$instance->$funcName->$class = new $class;
					}
				} else {
					error("不存在指定的文件类型");
				}
			}
		  
			return;
		}
	}
}
/*
 * 正常的错误处理
 * @param	int		$level		错误的号码
 * @param	string	$message	错误的内容
 * @param	string	$file		错误的文件名
 * @param	int		$line		错误的行号
 * @param	array	$context	错误的作用域的变量
 */
function myerror($level, $message , $file , $line , $context){
	//if($level === 2)return false;
	$delimite = "<br/>";
	if(isset($_SERVER['argc'])){
		$delimite = "\n";
	}
	$info = '错误号 : ' . $level . $delimite;
	$info .= '错误信息 : ' . $message .$delimite;	
	$info .= '错误的文件: ' . $file . ', ' . $line . '行' . $delimite;
	$info .= '发生时间 : '.date('Y-m-d H:i:s') . $delimite . $delimite;
	echo $info;
}
if(!function_exists('getInstance')){
	function getInstance()
	{
		return $frame;
	}
}
?>
