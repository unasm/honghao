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
		private function __construct(){}
		static $isInstanced;
		public static $model ,  $view , $lib , $obj;
		final public  static function &instance()
		{
			//单例模式
			if(!self::$obj instanceof self){
				self::$obj = new Loader;
				self::$obj->model = array();
			}
			return self::$obj;
		}
		public function __call($funcName , $args)
		{
			echo "funcName : " . $funcName . "<br/>";
			if($funcName == "model"){
				
			} elseif ($funcName === 'libraray') {
			
			} else if ($funcName === 'view') {
			
			}else{
			
			}
		}
	}
}
?>
