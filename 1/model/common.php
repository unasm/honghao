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
		static $obj;
		var $is_loaded;
		function __construct(){
			$this->is_loaded = array("sdfa");
		}
		final public  static function &instance()
		{
			//单例模式
			/*
			if(!self::$obj instanceof self){
				self::$obj = new Loader;
			}
			return self::$obj;
			 */
		}
		public function __call($funcName , $files ){
			$instance = &get_instance();
			if($funcName === 'config'){
				foreach($files as $class){
					if(array_key_exists($funcName , $this->is_loaded) && array_key_exists($class , $this->is_loaded[$funcName])){
						return;	
					}
					/*
					if(!$this->is_loaded[$funcName][$class])	{
						return;
					}
					 */
					include PATH_ROOT .$funcName . '/' .  $class . '.php';
					$this->is_loaded[$funcName][$class] = true;
					if(!isset($instance->config) || empty($instance->config)){
						$instance->config = array();
					}
					$instance->config = array_merge($instance->config , $config);
				}
			}else{
				$type = array('model' , 'library' , 'view' , 'config');
				if(in_array($funcName , $type)){
					foreach ($files as $class) {
						if(array_key_exists($funcName , $this->is_loaded) && array_key_exists($class , $this->is_loaded[$funcName])){
							return;	
						}
						include PATH_ROOT .$funcName . '/' .  $class . '.php';
						$this->is_loaded[$funcName][$class] = true;
						$instance->$class = new $class;
							//$instance->$funcName->$class = new $class;
					}
				} else {
					error("不存在指定的文件类型");
				}
			}
		  
			return;
			if($funcName == "model"){
				if(!$this->model[$funcName])	{
					$this->model[$funcName] = "value";
				}
				return $this->model[$funcName];
			} elseif ($funcName === 'library') {
			
			} else if ($funcName === 'view') {
			
			}else{
			
			}
		}
	}
}
if(!function_exists('getInstance')){
	function getInstance()
	{
		return $frame;
	}
}
?>
