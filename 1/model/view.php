<?php
/*************************************************************************
 * 这里封装smarty，显示文档页面使用
 * File Name :    ./core/view.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 这里讲数据和模板进行拼接
 */

function loadView($tpl , $data = array('title' => 'test.tianyi.com') , $flag = true){
	$tplBase = PATH_ROOT . 'client/html/' ;
	$smarty = new Smarty;
	$smarty->debugging = false;
	$smarty->caching = false;
	$smarty->cache_lifetime = 10;//以秒为单位的
	foreach($data as $key => $value){
		$smarty->assign($key , $value);
	}
	//$smarty->assign("title" , "这里是title");
	if($flag){
		$smarty->display($tplBase . "header.html");
	}
	$smarty->display($tplBase . $tpl);
	if($flag){
		$smarty->display($tplBase . "footer.html");
	}
}
/*
$data = array(
	'title' => 'title is here' , 
	'key' => "here is value"	
);
loadView("test.html" , $data);
 */
?>
