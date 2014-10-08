<?php
/*************************************************************************
 * File Name :    ./model/DataBaseModel.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 这里是处理数据库连接的地方
 * @todo 不同的表之间共享一个连接，但是操作的表却不能相同
 **/
class DataBaseModel
{
	static $link;	
	static $tableName;
	function __construct($table)
	{
		if(!$this->link){
			$this->init();
		}
		$this->tableName = $table;
	}
	protected function init()
	{

	}
	/**
	 * 插入到指定的表里面
	 */
	public function insert($data)
	{
	}
}
?>
