<?php
/*************************************************************************
 * File Name :    Encode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
//检测 字符串的编码格式
class Encode{
	
	/**
	 * 检测字符串是不是utf8编码格式
	 *
	 * @return boolen
	 **/
	protected function checkUtf8($str)
	{
		$len = strlen($str);
		for($i = 0;$i < $len; $i++){
			$c = ord($str[$i]);
			if($c > 128){
				if($c > 247)return false;
				elseif ($c > 239)$bytes = 4;
				elseif ($c > 223)$bytes = 3;
				elseif ($c > 191)$bytes = 2;
				else return false;
				if(($i + $bytes) > $len )return false;
				while($bytes > 1){
					$i++;
					$b = ord($str[$i]);
					if($b < 128 || $b > 191)return false;
					$bytes --;
				}
			}
		}
		return true;
	}

	/**
	 * 检查字符串的编码，调用文件的其他函数
	 *
	 * @return "UTF8,BIG5"
	 **/
	public function check()
	{
		if($this->checkUtf8($str)){
			return "UTF8";
		}
		return "false";
	}

	/**
	 * 检测顺序
	 **/
	public function test($str)
	{
		//mb_detect_encoding(urldecode($REQUEST_URI), 'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP, ISO-8859-1 , BIG-5, CP936 , CP950 , GB18030');
		echo mb_detect_encoding( $str, 'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP, ISO-8859-1') . "\n";
	}
}
$code = new Encode;
$str = file_get_contents("http://www.hkexnews.hk/listedco/listconews/advancedsearch/search_active_main_c.aspx");
echo $code->test($str ). "\n";
