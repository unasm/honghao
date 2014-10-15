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
	var  $tableName;
	function __construct($table)
	{
		if(!self::$link){
			$this->init();
		}
		$this->tableName = $table;
	}
	protected function init()
	{
		$this->dbConfig = $this->cofig['db'];
		self::$link = mysqli_init();
		$flag = self::$link->real_connect($this->dbConfig['host'] , $this->dbConfig['userName'] , $this->dbConfig['password'] , $this->dbConfig['dbName']);
		if(!$flag){
			exit("连接数据库失败");
		}
	}
	/**
	 * 插入到指定的表里面
	 * @param  array $tabItem	需要插入的列的名字，数组
	 * @param  array $data		需要添加的数据,array(array('asd' , 'asdf') , array('sdf'))这种格式
	 */
	public function insert($tabItem, $data)
	{
		if(!is_array($tabItem)){
			error("这里发送了错误，输入的数据不是数组");
		}
		if(!is_array($data)){
			error("输入数据不是数组");
		}

		$sql = 'INSERT INTO ' . $this->tableName;
		foreach($tabItem as $item){
			// count 的效率是O(1)的
			if(count($tabItem) != count($item)){
				error("需要插入的数据和对应的字段不同")	
			}	
			$sql .= ' values ( ' . implode(','). ')';
		}
		$result = self::$link->query($sql);
		if(!$result){
			error('mysql error : ' . self::$link->errno);
		}
		return $result;
	}
	/**
	 * 从指定的表里面获取对应的数据
	 * @param	string		$field	字符串按照a,b,c的方式拼接的字符串
	 * @param	array		$data	按照kv的格式组织的where限制条件
	 *
	 **/
	public function select($field , $data)
	{
		$sql = "select " . $field . " from {$this->tableName} where " , $this->getWhere($data);
		$result = self::$link->query($sql);
		if(!$result){
			error('mysql error : ' . self::$link->errno);
		}
		return $result->result_array();
	}
	/**
	 * 根据传入的array限制条件，拼接成对应的string
	 *
	 **/
	public function getWhere($data)
	{
		$sql = '';
		foreach($data as $key => $value){
			if(is_array($value)){
				$sql .= $key ,' = in(' , implode(',' , $value) ,') ';
			}else{
				$sql .= $key ,'=' , $value . ' ';	
			}
		}
		return $sql;
	}
	public function __destruct(){self::$link->close();}
}
?>
