<?php
/*************************************************************************
 * File Name :    ./config.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
$config['db']['host'] = "localhost";
$config['db']['userName'] = "root";
$config['db']['password'] = "douunasm";
$config['db']['dbName'] = "honghao";
//三个交易所的网页保存在哪个表里面
$config['shenpage'] = 'pages';
$config['gangpage'] = 'pages';
$config['hupage'] = 'hupage';
$config['gangpage'] = 'gangpage';

//07开始 dd结尾的长数字
$config['wx']['secret'] = "0723114f88d090f291fecbbb8dcf89dd";
$config['wx']['grant_type'] = "client_credential";
$config['wx']['appid'] = "wx656f0f60388c1c07";
$config['mc']['host'] = 'localhost';
$config['mc']['port'] = '11211';
?>
