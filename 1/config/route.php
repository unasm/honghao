<?php
/*************************************************************************
 * File Name :    ./config/route.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
$route['home'] = "/^((home)?\?code\=[\w\d]+)?$/";
$route['user'] = "/^(user\?uid\=[\d]+)?$/";
$route['user/index'] = "/^user\/index\?uid\=[\d]+$/";
$route['api/reply/send'] = "/^api\/reply\/send\?id\=[\d]+$/";
$route['api/favor/index'] = "/^api\/favor\/index\?status\=[\w]{3,10}&id=[\d]+$/";
$route['api/upload/index'] = "//";
$route['getcode/getAllShenCode'] = "//";
