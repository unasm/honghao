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
	function __construct()
	{
		if(!self::$link){
			$this->init();
		}
	}

	/**
	 * 清除数据库表里所有的数据
	 *
	 * @return boolen
	 **/
	public function truck()
	{
		$flag = self::$link->query('delete from ' . $this->tableName);
		if($flag){
			echo "删除成功";
		}
	}

	/**
	 * 创建表，如果还不存在的话
	 * @param string	$table 想要创建的表
	 * @todo  优化这个
	 **/
	public function createTable ($table)
	{
		$db = array(
			'code' => array(
				'code int unsigned not null default 0',
			),
		);
		if(array_key_exists($table , $db)){
			$db = $db[$table];
			$sql = 'create table IF NOT EXISTS ' . $table . ' (';
			foreach($db as $column){
				$sql .= $column;	
			}
			$sql .= ')';
			if(!self::$link->query($sql)){
				//error('创建表失败， mysql error : ' . self::$link->errno);
				echo "创建表失败 : " . mysqli_error(self::$link). "<br/>";
			}
		}else{
			trigger_error('the table you want does not exist' , E_USER_NOTICE);
		}
	}
	/**
	 * 设置当前对象操作的表名字
	 **/
	public function setTables($table)
	{
		$this->tableName = $table;
	}
	public static function init()
	{
		//$this->dbConfig = $this->cofig['db'];
		if(extension_loaded('mysqli')){
			self::$link = mysqli_init();
			$instance = &get_instance();
			if(!self::$link->real_connect($instance->config['db']['host'] ,
					$instance->config['db']['userName'] , 
					$instance->config['db']['password'] , 
					$instance->config['db']['dbName'])
			){
				exit("连接数据库失败 ,connect error is : " . self::$link->connect_error);
			}
		}else{
			die("please install mysqli");
		}
	}
	/**
	 * 插入到指定的表里面
	 * @param  array $tabItem	需要插入的列的名字，数组
	 * @param  array $data		需要添加的数据,array(array('asd' , 'asdf') , array('sdf'))这种格式
	 */
	public  function insert($tabItem, $data)
	{
		if(!is_array($tabItem)){
			error("这里发送了错误，输入的数据不是数组");
		}
		if(!is_array($data)){
			error("输入数据不是数组");
		}
		$sql = 'INSERT INTO `' . $this->tableName . '` ( `' . implode('`,`' , $tabItem) . '` ) VALUES';
		foreach($data as $row){
			// count 的效率是O(1)的
			if(count($tabItem) !== count($row)){
				trigger_error('需要插入的数据和字段数不同' , E_USER_NOTICE);
			}	
			$sql .= '(' . implode(' ,  ' , $row). ' ),';
		}
		//echo $sql . "<br/>";
		//var_dump(rtrim($sql , ','));
		$sql = rtrim($sql , ',' ) . ';';
		//die;
		$result = self::$link->query($sql);
		if(!$result){
			//error('mysql error : ' . self::$link->errno);
			error('mysql error : ' . self::$link->error);
		}
		return $result;
	}
	/**
	 * 从指定的表里面获取对应的数据
	 * @param	string		$field	字符串按照a,b,c的方式拼接的字符串
	 * @param	array		$data	按照kv的格式组织的where限制条件
	 *
	 **/
	public static function select($field , $data)
	{
		$sql = "select " . $field . " from {$this->tableName} where " . $this->getWhere($data);
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
				$sql .= $key .' = in(' . implode(',' . $value) .') ';
			}else{
				$sql .= $key .'=' . $value . ' ';	
			}
		}
		return $sql;
	}
	public function __destruct(){
		//self::$link->close();
	}
}
