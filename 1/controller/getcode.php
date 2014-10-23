<?php
/*************************************************************************
 * File Name :    ./getCode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 本文件主要是用来获取股票交易码的
 */
if(isset($_SERVER['argc'])){
//	include '../model/honghao.php';
//	include '../model/DataBaseModel.php';
}

abstract class Getcode  extends Honghao{
	public function __construct()
	{
		parent::__construct();
		$this->load->config('db');
		$this->load->model('BaseModelHttp');
		$this->load->model('HtmlParserModel');
	}
	public function makecode(){}
	public function getCompanyInfo(){}

	/**
	 * 检验是不是正确的，想要的深圳股票的返回页面
	 *
	 * @param $string $page		页面的html的string 
	 * @return boolen
	 **/
	public function checkPageRight($page)
	{
		$this->HtmlParserModel->parseStr($page);
		$class = $this->HtmlParserModel->find('.class');
		return true;
	}
	/**
	 * 根据传入的array 获取真正的页面
	 *
	 */
	public function createCode($prefix , $pos)
	{
		$res = array();
		$len = 3 - strlen($pos);
		while($len--) {
			$pos = '0'.$pos;
		}
		$page = 'pages';
		$this->DataBaseModel->createTable($page);
		//目前的都是6位的
		foreach($prefix as $code){
			$tmp = $code . $pos;
			$data = $this->DataBaseModel->select('content' , array( 'code' => $tmp));
			if(!$data || count($data) === 0 ){
				$page = $this->getCompanyInfo($tmp);
				$this->DataBaseModel->insert(
					array('code' , 'content'),
					array(
						array($tmp , $page)
					)
				) && die("插入成功");		
			} else{
				var_dump($data);
			}

			die;
			if($this->checkPageRight($page)){
				$res[] = $tmp;
			}
		}
		return $res;
	}

}
?>
