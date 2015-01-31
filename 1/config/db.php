<?php
/*************************************************************************
 * File Name :    ./config.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
if(PC){
//if(preg_match('/^bogon.*/' , gethostname())){
	$config['db']['host'] = "127.0.0.1";
	$config['db']['userName'] = "root";
	$config['db']['password'] = "asdf";
	$config['db']['dbName'] = "honghaotouzi";
} else {
	$config['db']['host'] = SAE_MYSQL_HOST_M . ':' . SAE_MYSQL_PORT;
	$config['db']['userName'] = SAE_MYSQL_USER ;
	$config['db']['password'] = SAE_MYSQL_PASS;
	$config['db']['dbName'] = SAE_MYSQL_DB;
//	$config['db']['port'] = SAE_MYSQL_PORT;
}
$config['delimate'] = "@";
//三个交易所的网页保存在哪个表里面
$config['shenpage'] = 'pages';
//$config['hupage'] = 'hupage';
$config['gangpage'] = 'pages';
$config['help'] = "请按照000001@2012Q2的格式输入查询,前面是股票的代码，后面2012Q2代表2012年第二季度，中间以@分割" ;
//07开始 dd结尾的长数字
$config['wx']['secret'] = "0723114f88d090f291fecbbb8dcf89dd";
$config['wx']['grant_type'] = "client_credential";
$config['wx']['appid'] = "wx656f0f60388c1c07";
$config['mc']['host'] = 'localhost';
$config['mc']['port'] = '11211';
?>
