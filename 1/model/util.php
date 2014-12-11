<?php
/*************************************************************************
 * 这里是整合了工具函数的类
 *
 * File Name :    ./util.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
class Util{
	/**
	 * 这里用来显示格式化数组
	 * @param array		$arr 数组
	 * @param string	$prefix  前面的空格
	 * @param int		$deep	深度，显示几层数组,默认100，一般不会有这么深
	 */
	function showArr($arr , $prefix = '' , $deep = 100){
		if($deep === 0){
			var_dump($arr);
			return;
		}
		foreach($arr as $key => $value){
			if(is_array($value)){
				echo $prefix . $key ."<br/>" . $prefix . "===><br/>";
				$this->showArr($value , $prefix ."&nbsp;&nbsp;&nbsp;&nbsp;" , $deep -1);
			}else {
				if($key){
					echo $prefix . $key . "&nbsp;&nbsp;==>&nbsp;&nbsp;" . $value . "<br/>";
				}else {
					echo $prefix . $value . "<br/>";
				}
			}
		}
	}
	/**
	 * 将text中的超链接转化成真正的可点击的链接
	 */
	function transLink($str){
		return preg_replace("/(http\:\/\/t\.cn\/[\d\w]{7})/" ,"<a href = '$1'>$1</a>" , $str );
	}
}
?>
