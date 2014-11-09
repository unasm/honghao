<?php
/*************************************************************************
 * File Name :    ./hugetcode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 获取沪市的交易信息
 **/
/*
if(isset($_SERVER['argc'])){
}
*/
if(!class_exists('Getcode')){
	require 'getcode.php';
}
define("DEBUG" , true);
class Hugetcode extends Getcode
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('DataBaseModel');
		//$this->load->model('BaseModelHttp');
	}
	/**
	 * 生成沪市的上市公司代码
	 */
	public function makecode()
	{		
		$this->DataBaseModel->setTables('pages');
		$flagTime = strtotime('1999-0-0');
		$prefix = "60";
		for($code = 0;$code <= 9999;$code++){
			$i = $code;
			$len = 6 - strlen($prefix) - strlen($i);
			while($len--){
				$i = '0' . $i;
			}
			$i = $prefix . $i;
			echo $i . "\n";
			flush();
			for($cnt = 0;$cnt < 16;){
				$end = $this->getTime('-' , $cnt);					
				$cnt +=3;
				$start = $this->getTime('-' , $cnt);
				$page = $this->getCompanyInfo(
					"http://query.sse.com.cn/security/stock/queryCompanyStatementNew.do?jsonCallBack=jsonpCallback67854&isPagination=0" . 
					"&productId={$i}&reportType2=DQBG&reportType=ALL&beginDate={$start}&endDate={$end}&_=1415495313414"
				);
				//reportType = ALL  ,全部，包括年报，半年报，季度报
				//reportType2 是定期公告还是临时公告
				$page = trim($page);
				if($page){
					//api接口一次吐出所有的数据，没有必要分页
					$this->DataBaseModel->insert(
						array('code' ,'content' ,'notice') , 
						//DQBG 是定期公告的意思，临时公告是LSGG
						array(array($i, $page , 'DQBG'))
					);
				}
				if((int)strtotime($end) < (int)$flagTime){
					break;
				}
			}
		}
	}
	function index(){
		$this->makecode();
	}
	/**
	 * 获得所有的沪市的code
	 *
	 * @return array
	 **/
	public function getAllhuCode()
	{
		$page = "";
		if(DEBUG){
			$page = file_get_contents('data.html', true)	;
		} else {
			//给出了固定的接口
			$page = $this->BaseModelHttp->get("http://www.sse.com.cn/js/common/ssesuggestdata.js;pv1057795e28c0ebf8",array() ,20);
		}
		$res = array();
		preg_match_all('/\(\{(.+)\}\)/' , $page , $arr);
		foreach($arr[1] as $data){
			$tmp = explode(',' , $data);
			$company = array();
			foreach($tmp as $row){
				$company[] = explode(':' , $row);
			}
			if(count($company) !== 3){
				error("Hugetcode/" .__LINE__ . "出现解析不为3的情况" ,E_ERROR);
			} else {
				$res[] = array(
					'code' => trim($company[0][1],"\""),
					'name' => trim($company[1][1] ,"\""),
					'undo' => trim($company[2][1], "\"")
				);
			}
		}
		return $res;
	}
	public	function getCompanyInfo($url = false){
		if(!$url){
			$url  = "http://query.sse.com.cn/security/stock/queryCompanyStatementNew.do?jsonCallBack=jsonpCallback67854&isPagination=true&productId=600001&reportType2=DQBG&reportType=ALL&beginDate=2008-10-29&endDate=2011-10-29&_=1415495313414";
		}
		$header = array(
			"Host: query.sse.com.cn" ,
			"Referer:http://www.sse.com.cn/assortment/stock/list/stockdetails/announcement/index.shtml?COMPANY_CODE=600000&productId=600000&reportType2=%E5%AE%9A%E6%9C%9F%E5%85%AC%E5%91%8A&reportType=ALL&moreConditions=false&startDate=2013-10-29&endDate=2014-10-29&reportType=ALL",
			//"Origin: http://www.sse.com.cn" , 
			"User-Agent: Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/37.0.2062.120 Chrome/37.0.2062.120 Safari/537.36" , 
			//"Accept: text/javascript, application/javascript, application/ecmascript, application/x-ecmascript, */*; q=0.01" ,
			"Accept: */*", 
			"Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.6,en;q=0.4",
			"Accept-Encoding: gzip,deflate,sdch" , 
		);
		$page = $this->BaseModelHttp->get(
			$url,
			//"http://www.sse.com.cn/assortment/stock/list/stockdetails/announcement/index.shtml?COMPANY_CODE=600000&startDate=2011-11-10&endDate=2014-11-09&productId=600300&startDate=2014-08-06&endDate=2014-11-06&reportType=ALL&reportType2=%E5%AE%9A%E6%9C%9F%E5%85%AC%E5%91%8A&reportType=ALL&moreConditions=true" , 
			//$url,
			$header , 200);
		return $page;
	}
	/**
	 * 从page数据库里面获取对应的内容
	 *
	 **/
	public function selectPage()
	{
		$this->DataBaseModel->setTables('pages');
		$data = $this->DataBaseModel->select('content , pid'  , array('pid' => 2018) );
		foreach($data as $lines){
			//var_dump(json_decode($lines['content']));
			preg_match("/^jsonpCallback67854\((.*)\)$/" , $lines['content'], $arr);
			$company = json_decode($arr[1] , true);
			foreach($company as $key => $value){
				echo $key . "<br/>";
			}
			//var_dump($company);
			//echo count($company[0]);
			die;
		}
	}
}
/*
$test = new Hugetcode();
$test->makecode();
 */
