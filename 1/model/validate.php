<?php
/*************************************************************************
 * File Name :    ../model/validate.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 检验数据是不是符合要求
 **/
class validate 
{
	/**
	 * 检验数据的有效性
	 *
	 * @param	string $value	想要检验的数值
	 * @param	string	$type	int/char/date等想要检验的类型
	 * @param	int		$max_length  value的最大的长度
	 * @return boolen
	 **/
	public function check($value, $type , $max_length = -1)
	{
		switch($type){
			case 'int' :
				if($max_length !== -1){
					$len = strlen($value);
					if($max_length < $len)return false;
				}
				return preg_match('/^\d+$/' , $value);
			case 'date':
				//return preg_match('/^\d{4}q\d$/' , $time);
			default :
				break;
		}
		error("type not found");
		return false;
	}
}
