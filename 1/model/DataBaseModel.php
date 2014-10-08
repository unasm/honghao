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
class DataBaseModel extends unasm
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
		$this->dbConfig = $this->cofig['db'];
		$this->link = mysqli_init();
		$flag = $this->link->real_connect($this->dbConfig['host'] , $this->dbConfig['userName'] , $this->dbConfig['password'] , $this->dbConfig['dbName']);
		if(!$flag){
			exit("连接数据库失败");
		}
	}
	/**
	 * 插入到指定的表里面
	 * @param  array $tabItem	需要插入的列的名字，数组
	 * @param  array $data		需要添加的数据
	 */
	public function insert($tabItem,$data)
	{
		if(!is_array($tabItem)){
			Debug::error("这里发送了错误，输入的数据不是数组");
		}
		if(!is_array($data)){
			Debug::error("输入数据不是数组");
		}
		if(count($tabItem) != count($data[0])){
			
		}
		$sql = 'INSERT INTO ' . $this->tableName . ' (';
		foreach($tabItem as $item){
		
		}
		$sql .= ')';
		foreach($data as $row){
			//将每一行的数据变成insert语句的内容
			if(count($row) != $len){
					
			}
		}
	}
	public function __destruct()
	{
		$this->link->close();
	}
}
?>
