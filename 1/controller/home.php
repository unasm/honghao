<?php
/*************************************************************************
 * File Name :    select.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 搜索获取对应的数据
 **/
DEFINE("DEBUG" , 0);
class Home extends Honghao
{
	
	function __construct()
	{
		parent::__construct()	;
		$this->load->model('DataBaseModel');
		$this->DataBaseModel->setTables('data');
		$this->load->model('validate');
	}

	/**
	 * 获取从微信来的信息，并解码
	 *
	 * @return get
	 **/
	public function index()
	{
		$this->load->model('output');
		$this->load->model('wx');
		$res = $this->wx->getInput();
		$out = array();
		$error = 0;
		if(!empty($res) && $res->Content){
			$data = explode($this->config['delimate'] , $res->Content);
			if(count($data) === 2){
				$_GET['code'] = trim($data[0]);
				$_GET['time'] = strtolower( trim($data[1]) );
				if(!preg_match('/^\d+$/' , $_GET['code'])){
					$this->output->formStr($_GET['code'] . $this->config['help'] . '1', $res);
					$error = 1;			
					return;
				}
				if(!preg_match('/^\d{4}q\d$/' , $_GET['time'])){
					$this->output->formStr($this->config['help'] . '2', $res);
					$error = 1;
				}
				if($error)return;
				$out = $this->getData();
				if(empty($out)){
					$this->output->formStr( "没有您想要的财报", $res);
					return;
				} 
				foreach($out as $idx => $value){
					$tmp =  "披露时间: " . $value['time'] . "\n\n";
					$tmp .= "<a href = 'http://www.honghaotouzi.sinaapp.com/index.php/home/index?code={$_GET['code']}&&time={$_GET['time']}'>" .$value['title']. "</a>\n";
					$tmp .="\n";
					$this->output->formStr($tmp , $res);
				}
			} else {
				$error = 1;
				$this->output->formStr($this->config['help'] . '3', $res);
			}
		} else {
			if(DEBUG){
				$_GET['code'] = '000001';
				$_GET['time'] = '2002Q2';
			}
			if($res){
				$this->output->formStr($this->config['help'] , $res);
			} elseif (isset($_GET['code']) && isset($_GET['time'])){
				//这种情况下，视为网页的正常访问
				$out = $this->getData();
				$this->showView($out);
			}
		}		

	}		

	/**
	* 获取查询的时间区间
	* 得到这个季度的开始和接下来两个季度的时间区间
	 *
	 **/
	public function getSelectTime($time)
	{
		$time = strtolower(trim($_GET['time']));
		$tmp = explode('q' , $time);
		if($tmp[0] === '0' || $tmp[1] > 4){
			error("输入的季度不对" , E_ERROR);
		}
		//$season = 3 * $tmp[1] - 2;
		$start = $tmp[0] . '-' . (3 * $tmp[1] - 2) . '-' . '00';

		$endSeason = 3 * $tmp[1] + 8;
		if($endSeason > 12){
			$tmp[0] += 1;
			$endSeason = $endSeason % 12;
		}
		return array('start' => strtotime($start) , 
			'end' =>strtotime($tmp[0] . '-' . ($endSeason) . '-' . '30') , 
			'q_num' => 'q' . $tmp[1]
			);
	}
	/**
	 * 根据传入的数据获取对应的结果
	 * @param	string/get	$time	2013Q3这种类型的数据
	 * @param	int/get		$code	股票的交易代码
	 * @return	array
	 **/
	public function getData()
	{

		//使用原生态的，避免麻烦
		//$code = $_GET['code'];
		$this->DataBaseModel->setTables('data');
		$res = $this->getSelectTime($_GET['time']);
		$data = $this->DataBaseModel->select("time ,link,did,title,code , q_num" ,  array() , " where code = '{$_GET['code']}' && timestamp < {$res['end']} && timestamp > {$res['start']} && q_num = '{$res['q_num']}'");
		var_dump($data);
		die;
		$res = array();
		//去重,数据中有重复
		for($i = 0 , $len = $data ? count($data) : 0 ; $i < $len ;$i++){
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
		return $res;
	}
	
	/**
	 * 将data表时间修改成为时间戳
	 *
	 * @return void
	 **/
	protected  function fixTime()
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

	/**
	 * 设置cache
	 *
	 **/
	public function cacheInit()
	{
		$this->DataBaseModel->createTable('cache');
	}
	/**
	 * 读取对应的数据
	 *
	 * @return string
	 **/
	protected function getCache($key)
	{
		$this->DataBaseModel->setTables('cache');
		$key = trim($key);
		$res = $this->DataBaseModel->select('value' , array('k' => $key));
		if($res){
			return $res[0]['value'];
		}
		return false;
		//return $res && $res[0]['value'];
	}
	/**
	 * 修改对应的配置
	 * @param string	$key	对应的key值
	 * @param string	$value	序列话之后的字符串
	 */
	protected function setCache($key , $value){
		$this->DataBaseModel->setTables('cache');
		if($this->getCache($key)){
			return $this->DataBaseModel->update(array('value' => $value) , array('k' => $key) );
		} else {
			return $this->DataBaseModel->insert(
				array('k' , 'value'),
				array(array($key , $value))
			);
		}
	}

	/**
	 * 获取目前的自定义菜单
	 *
	 **/
	public function getMenu()
	{
		$this->load->model('BaseModelHttp');
		$menu = json_decode($this->BaseModelHttp->get(
			"https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $this->getToken()
		) , true);
	}

	/**
	 * 给用户提示的按钮
	 * 用户点击按钮，返回一段话，这里就是那一段话的生成
	 **/
	public function help()
	{
		$this->load->model('output');
		$this->output->formStr("请输入股票代码以及财报时间,中间以{$this->config['delimate']}分开，如000001{$this->config['db']}2002Q2");
	}
	function testGetDateTime(){

		$_GET['time'] = '2002Q4'	;
		echo $_GET['time'] . "\n" ;
		$this->getData();
		$_GET['time'] = '2002Q3'	;
		echo $_GET['time'] . "\n" ;
		$this->getData();
		$_GET['time'] = '2002Q2'	;
		echo $_GET['time'] . "\n" ;
		$this->getData();
		$_GET['time'] = '2002Q1'	;
		echo $_GET['time'] . "\n" ;
		$this->getData();
	}

	/**
	 * 显示具体的页面
	 **/
	public function showView($output)
	{
		//echo "<a href = http://www.honghaotouzi.sinaapp.com/home/index?code={$_GET['code']}&&time={$_GET['time']} >sdfasdf</a>\n";
		//echo  PATH_ROOT . 'view/down.php';
		include PATH_ROOT . 'templates/down.php';
	}
}
