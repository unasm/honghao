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
	public function cleanTable()
	{
		$flag = self::$link->query('delete from ' . $this->tableName);
		if($flag){
			echo "delete success<br/>";
		}
	}
	
	/**
	 * 删除某一个表
	 *
	 * @return boolen
	 **/
	public function drop()
	{
		if(!self::$link->query('drop table ' . $this->tableName)){
			Debug::output('drop failed');
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
				'code char(10), ',//可以使用的股票的代码
				'id int unsigned not null auto_increment , ',
				'primary key(id) '
			),
			'pages' => array(
				'pid int unsigned not null auto_increment , ', 
				'code char(10) , ', //股票的代码,对的或者是不对的，全部存储起来
				'pageId tinyint not null default 1 ,',//页码，当前页面是这个code的第几页，考虑分页的问题
				'content text , ' , //网页的html内容，echo就是整个页面的
				'notice char(10) , ' , 
				'q_num char(5) not null , ' ,
				'primary key(pid)'
			),
			//array('time' , 'link' , 'size' , 'title' , 'notice' , 'code'), 
			'data' => array(
				'did int unsigned not null auto_increment , ',
				'time date  , ' ,
				'link varchar(100) , ' ,
				'size int unsigned not null default 0 ,',
				'title char(100) ,',
				'notice char(10) , ',
				'code char(10) , ' ,
				'timestamp int  not null default -1 ,' , 
				'q_num char(5) not null , ' ,
				'primary key(did)',
			),
			'cache' => array(
				'k char(15) not null ,' , 
				'value text ' ,
			)
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
				Debug::output("创建表失败 : " . mysqli_error(self::$link). "<br/>" , E_ERROR);
			}
			$this->setTables($table);
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
	/**
	 * 返回当前正在操作的表名字
	 **/
	public function getTable()
	{
		return $this->tableName ;
	}
	public static function init()
	{
		//$this->dbConfig = $this->cofig['db'];
		if(extension_loaded('mysqli')){
			self::$link = mysqli_init();
			if(!self::$link){
				die("database init failed");
			}
			$instance = &get_instance();
			/*
			if(array_key_exists('port' , $instance->config['db'])){
				$instance->config['db']['host'] = $instance->config['db']['host'] . ':' . $instance->config['db']['port'];
			}
			 */
			if(!self::$link->real_connect($instance->config['db']['host'] ,
					$instance->config['db']['userName'] , 
					$instance->config['db']['password'] , 
					$instance->config['db']['dbName'])
			){
				exit("连接数据库失败 ,connect error is : " . self::$link->connect_error);
			}
			self::$link->query("set names utf8");
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
		if(empty($data))return true;
		$sql = 'INSERT INTO `' . $this->tableName . '` ( `' . implode('`,`' , $tabItem) . '` ) VALUES ';
		foreach($data as $row){
			// count 的效率是O(1)的
			if(count($tabItem) !== count($row)){
				trigger_error('需要插入的数据和字段数不同' , E_USER_NOTICE);
			}	
			$sql .= "(";
			foreach($row as $field){
				$sql .= "'" . self::$link->real_escape_string($field) ."',";
			}
			$sql = rtrim($sql , ',' ) . ') ,';
		}
		$sql = rtrim($sql , ',' ) . ';';
		$result = self::$link->query($sql);
		if(!$result){
			//error('mysql error : ' . self::$link->errno);
			echo "\n" . $sql . "\n";
			error('insert mysql error : ' . mysqli_error(self::$link));
			//echo "创建表失败 : " . mysqli_error(self::$link). "<br/>";
		}
		//mysqli_free_result($result);
		return true;
	}
	/**
	 * 从指定的表里面获取对应的数据
	 * @param	string		$field	字符串按照a,b,c的方式拼接的字符串
	 * @param	array		$data	按照kv的格式组织的where限制条件
	 * @param	string		$where	和data构成不能共存的限制条件
	 **/
	public function select($field , $data = array() , $where = false)
	{
		$sql = '';
		if($where){
			$sql = "select " . $field . " from {$this->tableName} " .$where ;
		} else {
			$sql = "select " . $field . " from {$this->tableName} " . $this->getWhere($data);
		}
		$result = self::$link->query($sql);
		if(!$result){
			error('select mysql error : ' . mysqli_error(self::$link));
		}
		if($result->num_rows === 0)return array();
		return $this->getResult($result);

		return $rows;
	}

	/**
	 * 根据句柄，获取对应的数据
	 * @param	link	$resut	句柄，mysql的返回的东西	
	 * @return array
	 **/
	public function getResult($result)
	{
		$rows = array();
		while($row = $result->fetch_assoc()){
			$rows[] = $row;
		}
		mysqli_free_result($result);
		return $rows;
	}
	/**
	 * 根据传入的array限制条件，拼接成对应的string
	 *
	 **/
	public function getWhere($data)
	{
		if(empty($data))return '';
		$sql = 'where ';
		$cnt = 1;
		if(!is_array($data))return $data;
		foreach($data as $key => $value){
			if(!$cnt){
				$sql .= ' && ';	
			} else {
				$cnt = 0;
			}
			if(is_array($value)){
				//有待测试
				$tmp = '\'' . implode('\',\'' , $value) . '\'';
				$sql .= $key .' in( ' . $tmp . ') ';
			}else{
				$sql .= $key .' = \'' . $value . '\'';	
			}
		}
		return $sql;
	}

	/**
	 * 修改已经有的变量
	 * @param	array	$data  想要修改的变量kv
	 * @param	string	$where	限制条件
	 * @example	 (array('key' => 'test' , value => ''hello,world) , key => 'sdf' )
	 * @example	 $where 为数组的时候，为精确查找，也可以传入包含字符串
	 **/
	public function update ($data , $where)
	{
		if(!is_array($data)){
			die("请输入数组");
		}
		$sql = 'update ' . $this->tableName;
		$tmp = '';
		foreach($data as $key => $value){
			if($tmp){
				$tmp .= ' && set ' . $key . " = '" . $value . "'";	
			} else {
				$tmp = ' set ' . $key . " = '" . $value . "'";
			}
		}
		$sql .= $tmp . ' ' . $this->getWhere($where);
		$flag = self::$link->query($sql);
		if(!$flag){
			//error('创建表失败， mysql error : ' . self::$link->errno);
			Debug::output("更新表失败 : " . mysqli_error(self::$link). "<br/>" , E_ERROR);
		}
		return true;
	}
	
	function delete ($did){
		return self::$link->query( 'delete from ' . $this->tableName . ' where did = ' . $did );
	}
	/**
	 * 传入一条sql，无条件执行
	 *
	 * @return array
	 **/
	public function exec($sql)
	{
		return $this->getResult(self::$link->query($sql));
	}
	public function __destruct(){self::$link->close();}
}
