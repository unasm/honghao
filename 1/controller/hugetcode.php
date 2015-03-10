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
	 * 获取正确的公司代码
	 * 
	 * @param	int		$i	当前公司代码的加权部分
	 * @return string
	 **/
	public function getStockCode($i , $prefix)
	{
		$len = 6 - strlen($prefix) - strlen($i);
		while($len--){
			$i = '0' . $i;
		}
		return $prefix . $i;
	}
	/**
	 * 生成沪市的上市公司代码
	 */
	public function makecode()
	{		
		$this->DataBaseModel->setTables('pages');
		$flagTime = strtotime('1999-0-0');
		$prefix = "60";
		//reportType = ALL  ,全部，包括年报，半年报，季度报
		//DQBG 是定期公告的意思，临时公告是LSGG
		$typeArr = array('YEARLY' => 'q4' , 'QUATER1' => 'q1' , 'QUATER2' => 'q2' , 'QUATER3' => 'q3');
		//上海股票最大的代码是3998
		for($code = 0;$code <= 4400;$code++){
			$stockCode = $this->getStockCode($code , $prefix);
			for($cnt = 0;$cnt < 20;){
				$end = $this->getTime('-' , $cnt);					
				$cnt +=3;
				$start = $this->getTime('-' , $cnt);

				echo $stockCode . ' ==> ' . $end . "\n";
				flush();
				$res = array();
				$data = $this->DataBaseModel->select('pid'  , array() , 'where code = ' . $stockCode . ' && pageId = ' . $cnt);
				if(!$data || empty($data)){
					foreach($typeArr as $type => $qNum){
						$page = $this->getCompanyInfo(
							"http://query.sse.com.cn/security/stock/queryCompanyStatementNew.do?jsonCallBack=jsonpCallback67854&isPagination=0" . 
							"&productId={$stockCode}&reportType2=DQBG&reportType={$type}&beginDate={$start}&endDate={$end}&_=1415495313414"
						);
						if(count($this->decode($page))){
							$res[] = array($stockCode, $page , $type  , $qNum , $cnt);
						}
					}
					//api接口一次吐出所有的数据，没有必要分页
					$this->DataBaseModel->insert(
						array('code' ,'content' ,'notice' , 'q_num' , 'pageId') , 
						$res
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
	 * 对获取的数据进行解码，判断是不是有具体内容的
	 *
	 * @return array
	 * @author Me
	 **/
	protected function decode($page)
	{
		preg_match("/^jsonpCallback67854\((.*)\)$/" , $page, $arr);
		if(count($arr) != 2){
			var_dump($page);
			echo  "得到的arr != 2\n";
			return array();
		}
		$company = json_decode($arr[1] , true);
		if(!array_key_exists('result' , $company)){
			die("没有result\n");
		}
		return $company['result'];
	}

	/**
	 * 获取每一行数据
	 *
	 * @return array
	 **/
	public function getPageRows($company , $lines)
	{
		$result = array();
		$url = "http://www.sse.com.cn";
		foreach($company as $row){
			$tmp = array();
			if(isset($row['SSEDate']) && isset($row['title']) && 
				isset($row['security_Code']) && isset($row['URL'])){
					$tmp = array(
						$row['SSEDate'], 
						$url . $row['URL'], 
						$row['title'] , 
						$row['security_Code'] ,
						$lines['notice'] ,
						$lines['q_num'],
						strtotime($row['SSEDate']),
					);
				$result[] = $tmp;
				//echo $row['security_Code'] . $row['SSEDate'] ." " . $row['title'] . "\n";
				flush();
			} else{
				echo $lines['pid'] . "\n\n";
				die("sdf");
			}
		}
		return $result;
	}
	/**
	 * 从page数据库里面获取对应的内容
	 *
	 **/
	public function selectPage()
	{
		$this->DataBaseModel->setTables('pages');
		//hu的pages是从2018开始的
		//$data = $this->DataBaseModel->select('content , pid'  , array('pid' => 5457)  );
		//$data = $this->DataBaseModel->select('notice, q_num,content , pid'  , array() , ' where code >= 600000 && code <= 609999 ');
		$data = $this->DataBaseModel->select('notice, q_num,content , pid'  , array('notice' => array('YEARLY' , 'QUATER1' , 'QUATER2' , 'QUATER3')));
		//$typeArr = array('YEARLY' => 'q4' , 'QUATER1' => 'q1' , 'QUATER2' => 'q2' , 'QUATER3' => 'q3');
		$this->DataBaseModel->setTables('data');
		foreach($data as $lines){
			echo $lines['notice'] . "\n";
			$company = $this->decode($lines['content']);
			$res = $this->getPageRows($company , $lines);
			$this->DataBaseModel->insert(
				array('time' , 'link' , 'title' , 'code' , 'notice' , 'q_num' , 'timestamp') , $result
			) && printf("yes all\n\n");
		}
	}

	/**
	 * 刷新数据
	 * 抓取最新的数据
	 *
	 * @return boolen
	 **/
	public function refresh(){
		$this->DataBaseModel->setTables('data');
		$prefix = "60";
		//reportType = ALL  ,全部，包括年报，半年报，季度报
		//DQBG 是定期公告的意思，临时公告是LSGG
		$typeArr = array('YEARLY' => 'q4' , 'QUATER1' => 'q1' , 'QUATER2' => 'q2' , 'QUATER3' => 'q3');
		//上海股票最大的代码是3998
		for($code = 0;$code <= 4400;$code++){
			$stockCode = $this->getStockCode($code , $prefix);
			$end = $this->getTime('-' , 0);					
			$start = $this->getTime('-' , 1);
			echo $stockCode . ' ==> ' . $start . "\n";
			flush();
			$res = array();
			foreach($typeArr as $type => $qNum){
				$page = $this->getCompanyInfo(
					"http://query.sse.com.cn/security/stock/queryCompanyStatementNew.do?jsonCallBack=jsonpCallback67854&isPagination=0" . 
					"&productId={$stockCode}&reportType2=DQBG&reportType={$type}&beginDate={$start}&endDate={$end}&_=1415495313414"
				);
				$res = $this->getPageRows( $this->decode($page) , array('notice' => $type , 'q_num' => $qNum) );
				foreach($res as $data){
					$stored = $this->DataBaseModel->select(
						' * '  , 
						array('code' => $stockCode , 'q_num' => $qNum , 'time' => $data[0] )
					);	
					$flag = 0;
					foreach($stored as $row){
						if( trim($row['title']) == trim($data[2])){
							$flag = 1;
						}
					}
					if($flag === 0){
						echo "new data\n";
						var_dump($data);
						$rs = $this->DataBaseModel->insert(
							array('time' , 'link' , 'title' , 'code' , 'notice' , 'q_num' , 'timestamp') , array($data)
						);
						var_dump($rs);
					}
				}			
			}
		}
	}
}
