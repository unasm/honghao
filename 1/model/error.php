<?php
/*************************************************************************
 * File Name :    ./error.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
function error($string, $type = 0 , $destination  = "jiamin1@staff.sina.com.cn", $headers = "From: webmaster@test.tianyi.com"){
	error_log($string,$type,$destination,$headers);
	exit($string);
}
?>
