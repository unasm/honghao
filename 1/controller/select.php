<?php
/*************************************************************************
 * File Name :    select.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 搜索获取对应的数据
 **/
class Select extends Honghao
{
	
	function __construct()
	{
		parent::__construct()	;
		$this->load->model('DataBaseModel');
		$this->DataBaseModel->setTables('data');
		$this->load->model('validate');
	}
	
	/**
	 * 根据传入的数据获取对应的结果
	 * @param	string/get	$time	2013Q3这种类型的数据
	 * @param	int/get		$code	股票的交易代码
	 * @return	array
	 **/
	public function index()
	{
		$_GET['code'] = '000001';
		$_GET['time'] = '2002Q2';
		//使用原生态的，避免麻烦
		$code = trim($_GET['code']);
		if(!$this->validate->check($code , 'int' , 6)){
			output("输入的编号不对");
		}
		$time = strtolower(trim($_GET['time']));
		if(!preg_match('/^\d{4}q\d$/' , $time)){
			error("输入的时间格式不对" , E_ERROR);
		}
		$this->DataBaseModel->setTables('data');
		$tmp = explode('q' , $time);
		if($tmp[1] > 4){
			error("输入的季度不对" , E_ERROR);
		}
		$start = strtotime($tmp[0] . '-' . ( 3 * $tmp[1] - 2) . '-' . '00');
		$end  = strtotime($tmp[0] . '-' . ( 3 * $tmp[1]) . '-' . '30');
		$data = $this->DataBaseModel->exec("select time ,link,did,title,code from " . $this->DataBaseModel->getTable() . " where code = {$code} && timestamp < {$end} && timestamp > {$start}");
		//进行排重

		$res = array();
		//去重,数据中有重复
		for($i = 0 , $len = count($data); $i < $len ;$i++){
			$flag = 1;
			for($j = $i+1; $j < $len;$j++){
				$tmpflag = 1;
				//echo $j . "<br/>";
				foreach($data[$i] as $key => $value){
					//did ，自增不比较
					if($key === 'did'){
						continue;
					}
					if(trim($data[$i][$key]) !== trim(($data[$j][$key]))){
						$tmpflag = 0;
						break;
					}
				}
				//出现了完全相同搞得情况，证明,不是想要的
				if($tmpflag){
					$flag = 0;
					break;
				}
			}
			if($flag){
				$res[] =  $data[$i];
			}
		}
		output($res);
	}

	/**
	 * 将时间修改成为时间戳
	 *
	 * @return void
	 **/
	public function fixTime()
	{
		$data = $this->DataBaseModel->select('did,time' ,array());
		foreach($data as $row){
			if(preg_match("/^\d{4}-\d{2}-\d{2}$/" , $row['time'])){
				$this->DataBaseModel->update(
					array('timestamp' => strtotime($row['time'])),
					array('did' => $row['did'] )
				);
				echo $row['did'] . "\n";
			} else {
				echo 'error: ' . $row['did'] . "\n";
				die;
			}
		}
	}
}
