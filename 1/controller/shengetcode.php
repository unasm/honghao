<?php
/*************************************************************************
 * File Name :    ./shengetcode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
if(!class_exists('Getcode')){
	require 'getcode.php';
}
/**
 * 具体实现深圳股市的实现
 **/
class Shengetcode extends Getcode
{
	function __construct(){
		parent::__construct();
		$this->load->model('DataBaseModel');
	}

	/**
	 * 是不是应该保存,有没有对应的公司
	 * @param	string	$page	网页的html代码
	 **/
	public function checkPageRight($page)
	{
		$this->HtmlParserModel->parseStr($page);
		$class = $this->HtmlParserModel->find('.page12');
		if(count($class) === 1){
			$class = $class[0]->value;
			preg_match_all("/\<span\>\S*(\d+)\S*\</s" , $class , $value);
			if(count($value) === 2){
				//有两个数字，一个是总共的页数，一个是当前的页数
				return array('now' => $value[1][0] , 'total' => $value[1][1]);
			} elseif(trim($page)) {
				var_dump($page);
				Debug::output('Shengetcode/' . __LINE__ . '出现了count  != 2的情况' , E_ERROR);
			}
		} elseif(trim($page)) {
			var_dump($page);
			Debug::output('shenggetcode/' . __LINE__ . '出现了>1的错误'  , E_ERROR);
		}
		return false;
	}
	/**
	 * 生成深圳上市公司的代码
	 * 生存代码，并抓取对应的页面，如果有多个页面，一并抓取，是为了不再定向查找，方便接下来解析所有的页面;
	 */
	public function makeCode()
	{
		//深市A股，B股,配股
		//$this->DataBaseModel->createTable('code');
		//上市公司的前缀
		$prefix = array('000' , '200' ,'080' ,'031');

		//010301 年度报告
		//010303 半年度报告
		//010305 一季度报告
		//010307 三季度报告
		//$notice = array('010301' , '010303' , '010305' , '010307');
		$notice = array('010301' => 'q4', '010303' => 'q2' , '010305' => 'q1' , '010307' => 'q3');
		$this->DataBaseModel->createTable($this->config['shenpage']);
		for($i = 2;$i <= 999 ;$i++){
			foreach($prefix as $pre){
				//结合成完整的stockCode
				$stockCode = $this->getStockCode($i, $pre);
				foreach ($notice as $key => $value) {
					//不足三位，前面补充0,确保最终是6位
					$res = $this->createCode($stockCode , $key , $value);
					if(!empty($res)){
						for($j = 0,$len = count($res);$j < $len;$j++){
							array_push($res[$j] , $value);
						}
						if($this->DataBaseModel->insert(					
							array('code' , 'content' , 'pageId' , 'notice' , 'q_num'),
							$res
						)){
							echo $stockCode . "\n";
							flush();
						}
					}
				}
			}
		}
	}

	/**
	 * 获取对应的股票的代码
	 *
	 * @return string
	 **/
	public function getStockCode($i, $prefix)
	{
		$len = 6 - strlen($prefix) - strlen($i);
		while($len--) {
			$i = '0'.$i;
		}
		return $prefix . $i;
	}
	/**
	 * 根据传入的array 获取真正的页面
	 * @param string	$stockCode	想要检测的code
	 * @param int		$notice		年报的类型
	 * @notice 这里没有香港的类型
	 */
	public function createCode($stockCode , $notice , $qNum){
		$res = array();
		$data = $this->DataBaseModel->select('pid ' , array( 'code' => $stockCode , 'notice' => $notice));
		if(!$data || count($data) === 0 ){
			//没有数据的情况下
			$page = trim($this->getCompanyInfo($stockCode , $notice));
			$pageState = $this->checkPageRight($page);
			if($pageState && $pageState['now']){
				//0,0的情况不保存
				$res[] = array($stockCode , base64_encode($page) , 1 , $notice);
				for($i = $pageState['now'] + 1; $i <= $pageState['total'];$i++){
					//arr的顺序是stockCode , pageContent , page 页码 , notice;
					$res[] = array(
								$stockCode , 
								base64_encode($this->getCompanyInfo($stockCode , $notice , $i)) 
								,$i 
								, $notice
							);
				}
				//Debug::output('for insert page all : ' . $stockCode , E_NOTICE);
			} else {
				//Debug::output('checkPageRight返回为false ,此时的code 为' . $tmpCode , E_NOTICE );
			}
		} else {
			//Debug::output('之前已经获取数据成功' , E_NOTICE);
			foreach($data as $row){
				if($this->DataBaseModel->update(array('q_num' => $qNum) , array('pid' => $row['pid']))){
					echo "update success\n";
				}
			}
		}
		return $res;
	}

	/**
	 * 检验输入的值是否正确,网页的编码，数据的编码
	 *
	 * @return void
	 * @author Me
	 **/
	public function check()
	{
		//$this->DataBaseModel->setTables('data');
		$this->DataBaseModel->setTables('data');
		$data = $this->DataBaseModel->select('title');
		$data[0]['title'] = mb_convert_encoding($data[0]['title'], 'UTF-8', 'gbk');
		//var_dump($data[0]['title']);
	}
	/**
	 * 根据对应的code获取对应公司的财报，不过这里只是包括了深圳证券交易所的
	 * @param string	$code	上市公司的代码
	 * @param string	$notice	年报的类型
	 * @param int		$pageNo 页码
	 **/
	public function getCompanyInfo($code = "000001" , $notice = "010301" , $pageNo = 1)
	{
		$args['stockCode'] = $code;
		$args['keyword'] = "";
		$args['noticeType'] = $notice;
		$args['startTime'] = "2001-01-01";
		//这里的时间将来要修改
		$args['endTime'] = $this->getTime('-');
		$args['imageField'] = array("x"  => 0 , 'y' => 97);
		$args['pageNo'] = $pageNo;
		//$args['imageField'] = 17;
		/* $args['tzy'] = "";
		 */
		$header = array(
			"Host: disclosure.szse.cn" ,
			"Referer: http://disclosure.szse.cn/m/drgg.htm"	, 
			"Origin: http://disclosure.szse.cn" , 
			"User-Agent: Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/37.0.2062.120 Chrome/37.0.2062.120 Safari/537.36" , 
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8" ,
			"Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.6,en;q=0.4"
		);
		$cookie = "JSESSIONID=F65D13DEB783C6AA721BCBB784AB1066";
		$page =  $this->BaseModelHttp->post("http://disclosure.szse.cn/m/search0425.jsp" , $args, $header , 10 , $cookie );
		return $page;
	}
	/**
	 * 从$url中获取对应的股票交易码,不过只有上证和深证的
	 * @deprecated 已经废弃,当作参考使用而已
	 */
	function getCode(){
		$code = array();
		$url = "http://www.dqw.cn/007gupiao/zonghe/daima.htm";
		$fileName = basename($url);
		$data = "";
		if(file_exists(BasePath . 'cache/' . $fileName)){
			$data = file_get_contents(BasePath . 'cache/' . $fileName ,true)	;
		}else{
			$data = $this->BaseModelHttp->curl(array('url' => $url));
		}
		$parser = new HtmlParserModel();
		$parser->parseStr($data);
		$nodes = $parser->find('.value');
		$comName = array("上证主板" , "深证主板" , "深中小板" , "深创业板" , "上证B股" , "深证B股");
		foreach($nodes as $key =>  $company){
			$dataValue = split("\<|\>" , $company->value);
			$result = array();
			for($i = 0 ,$len = count($dataValue);$i < $len;$i++){
				$tmp = trim($dataValue[$i]);
				if(preg_match('/^\d+\s/' ,$tmp)){
					$tmp = split(" " , $tmp);
					if(count($tmp === 2)){
						$result[] = array('code' => $tmp[0] , 'name' => $tmp[1]);
					}
				}
			}
			//$code[$key] = $result ;
			if($key === 0 || $key === 4){
				$this->shangCode[] = $result;
			}else{
				$this->shenCode[] = $result;
			}
		}
	}

	/**
	 * 修改data中的title 编码，以免将来乱码
	 *
	 * @return void
	 * @author Me
	 **/
	public function trans()
	{
		$this->DataBaseModel->setTables('data');
		$datas = $this->DataBaseModel->select('did,title');
		$cnt = 0;
		foreach ($datas as $row) {
			$cnt++;
			if($cnt < 2)continue;
			$this->DataBaseModel->update(
				array(
					'title' => "sdfa色风俗地方" 
					//'title' => mb_convert_encoding($row['title'] , 'UTF-8' , 'GBK')
				),
				array(
					'did' => 1 
				)
			);
		}
	}
}
