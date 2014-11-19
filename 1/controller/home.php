<?php
/*************************************************************************
 * File Name :    select.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 搜索获取对应的数据
 **/
DEFINE("DEBUG" , 1);
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
		/*
		$_GET['time'] = "2002Q2";
		$_GET['code'] = '000001';
		 */
		$out = array();
		if(!empty($res) && $res->Content){
			$data = explode($this->config['delimate'] , $res->Content);
			if(count($data) === 2){
				$_GET['code'] = $data[0];
				$_GET['time'] = $data[1];
				if(!$this->validate->check($_GET['code'] , 'int' , 6)){
				//	output("输入的编号不对");
					$this->output->formStr($this->config['help'] . '1', $res);
				}
				$_GET['time'] = strtolower($_GET['time']);
				if(!preg_match('/^\d{4}q\d$/' , $_GET['time'])){
					$this->output->formStr($this->config['help'] . '2', $res);
				}
				$out = $this->getData();
			} else {
				$this->output->formStr($this->config['help'] . '3', $res);
			}
		} else {
			if($res){
				$this->output->formStr("请输入具体的查询内容" , $res);
			} elseif (isset($_GET['code']) && isset($_GET['time'])){
				$out = $this->getData();
			}
		}
		/*
		$this->view('index.html' , $out[0]);
		return;
		 */
		$this->output->formStr($out , $res);
	}		
	/**
	 * 根据传入的数据获取对应的结果
	 * @param	string/get	$time	2013Q3这种类型的数据
	 * @param	int/get		$code	股票的交易代码
	 * @return	array
	 **/
	public function getData()
	{
		/*
		$_GET['code'] = '000001';
		$_GET['time'] = '2002Q2';
		 */
		//使用原生态的，避免麻烦
		$code = trim($_GET['code']);
		$time = strtolower(trim($_GET['time']));
		$this->DataBaseModel->setTables('data');
		$tmp = explode('q' , $time);
		if(count($tmp) > 1 && $tmp[1] > 4){
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
		$out = array();
		foreach($res as $idx => $value){
			$tmp =  "披露时间: " . $value['time'] . "\n";
			$tmp .= "http://www.baidu.com\n";
			$tmp .= "<a href = '". $value['link']."'>" .$value['title']. "</a>\n";
			$tmp .="\n";
			$tmp .= $value['link'];
			if(DEBUG){
				//$tmp = "honghaotouzi.sinaapp.com/index.php/home/show";
				$tmp = "<a href = 'http://mp.weixin.qq.com/mp/redirect?url=http://disclosure.szse.cn/finalpage/2002-04-18/573256.PDF#weixin.qq.com#wechat_redirect'>tesing</a>";
			}
			$out[] = $tmp;
		}
		return $out;
		//output($res);
	}
	
	function show(){
		$this->view('index.html');
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
	public function getCache($key)
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
	public function setCache($key , $value){
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
}
