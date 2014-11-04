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
	//public function getCompanyInfo(){}

	/**
	 * 检验是不是正确的，想要的深圳股票的返回页面
	 *
	 * @param $string $page		页面的html的string 
	 * @return boolen
	 **/
	public function checkPageRight($page){}
	/**
	 * 根据传入的array 获取真正的页面
	 * @param array		$prefix		深圳公司的上市公司代码前缀
	 * @param int		$pos		从0～999已经扫描到的下表
	 * @param int		$notice		年报的类型
	 * @notice 这里没有香港的类型
	 */
	public function createCode($prefix , $pos , $notice){
		$res = array();
		foreach($prefix as $code){
			$tmpCode = $code . $pos;
			$data = $this->DataBaseModel->select('pid ' , array( 'code' => $tmpCode , 'notice' => $notice));
			if(!$data || count($data) === 0 ){
				$page = trim($this->getCompanyInfo($tmpCode , $notice));
				$pageState = $this->checkPageRight($page);
				if($pageState && $pageState['now']){
					//0,0的情况不保存
					$this->DataBaseModel->insert(
						array('code' , 'content' , 'notice'),
						array(
							array($tmpCode , base64_encode($page) , $notice)
						)
					) && printf ("insert success");	
					for($i = $pageState['now'] + 1; $i <= $pageState['total'];$i++){
						$this->DataBaseModel->insert(
							array('code' , 'content' , 'pageId' , 'notice'),
							array(
								array($tmpCode , base64_encode($this->getCompanyInfo($tmpCode , $notice)) ,$i , $notice)
							)
						);
					}
					Debug::output('for insert page all : ' . $tmpCode , E_NOTICE);
				} else {
					//Debug::output('checkPageRight返回为false ,此时的code 为' . $tmpCode , E_NOTICE );
				}
				echo $tmpCode . "\n";
			} else {
				Debug::output('之前已经获取数据成功' , E_NOTICE);
			}
		}
	}
}
?>
