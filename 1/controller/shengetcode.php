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
define("DEBUG" , 1);
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
		//抓取遗漏的数据
		$prefix = array('000' , '200' ,'300' , '900' , '002' ,'301' , '311');
		// 301 , 311 是以后会增加的
		//010301 年度报告
		//010303 半年度报告
		//010305 一季度报告
		//010307 三季度报告
		//$notice = array('010301' , '010303' , '010305' , '010307');
		$notice = array('010301' => 'q4', '010303' => 'q2' , '010305' => 'q1' , '010307' => 'q3');
		$this->DataBaseModel->createTable($this->config['shenpage']);
		for($i = 0;$i <= 999 ;$i++){
			foreach($prefix as $pre){
				//结合成完整的stockCode
				$stockCode = $this->getStockCode($i, $pre);
				$res = array();
				foreach ($notice as $key => $value) {
					$tmp = $this->createCode($stockCode , $key , $value);
					if(!empty($tmp)){
						for($j = 0,$lenj = count($tmp); $j < $lenj; $j++){
							$res[] = $tmp[$j];
						}
					}
				}
				if(!empty($res)){
					if($this->DataBaseModel->insert(					
						array('code' , 'content' , 'pageId' , 'notice' , 'q_num'),
						$res )
					) {
						echo $stockCode . "\n";
						flush();
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
	 * @return	页面的数组
	 */
	public function createCode($stockCode , $notice , $qNum){
		$res = array();
		$data = $this->DataBaseModel->select('pid ' , array( 'code' => $stockCode , 'notice' => $notice));
		if(!$data || count($data) === 0 ){
			//没有数据的情况下
			$args = array();
			$args['stockCode'] = $stockCode;
			$args['noticeType'] = $notice;
			$args['startTime'] = "2001-01-01";
			$page = trim($this->getCompanyInfo($args));

			$pageState = $this->checkPageRight($page);
			if($pageState && $pageState['now']){
				//0,0的情况不保存
				$res[] = array($stockCode , base64_encode($page) , 1 , $notice , $qNum);
				for($i = $pageState['now'] + 1; $i <= $pageState['total'];$i++){
					//arr的顺序是stockCode , pageContent , page 页码 , notice;
					$args['pageNo'] = $i;
					$res[] = array(
								$stockCode , 
								base64_encode($this->getCompanyInfo($args)) 
								,$i , $notice , $qNum
							);
				}
				//Debug::output('for insert page all : ' . $stockCode , E_NOTICE);
			} else {
				//Debug::output('checkPageRight返回为false ,此时的code 为' . $tmpCode , E_NOTICE );
			}
		} else {
			Debug::output('之前已经获取数据成功' , E_NOTICE);
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
	//public function getCompanyInfo($code = "000001" , $notice = "010301" , $pageNo = 1)
	public function getCompanyInfo($args)
	{
		//$args['stockCode'] = $code;
		$args['keyword'] = "";
		//$args['noticeType'] = $notice;
		if(!isset($args['startTime'])){
			$args['startTime'] = "2001-01-01";
		}
		//这里的时间将来要修改
		if(!isset($args['endTime'])){
			$args['endTime'] = $this->getTime('-');
		}
		$args['imageField'] = array("x"  => 0 , 'y' => 97);
		if(!isset($args['pageNo'])){
			$args['pageNo'] = '1';
		}
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

	/**
	 * 获取页面内部的数据
	 *
	 * @return array
	 **/
	public function getPageRows($str , $page )
	{
		$res = array();
		$this->HtmlParserModel->parseStr($str , array() , "big5");
		$lines = $this->HtmlParserModel->find('.td2');
		$baseUrl = "http://disclosure.szse.cn/";
		foreach($lines as $line){
			$tmpStr = $line->value;
			// <span class="link1">[2014-10-24]</span>
			preg_match('/\>\[(\d{4}-\d{2}-\d{2})\]/' , $tmpStr , $time);
			if(count($time) != 2){
				var_dump($tmpStr);
				echo "<br/>";
				Debug::output('time is wrong' , E_ERROR);
			}
			//$tmpStr = "<a href=\"finalpage/2014-03-07/63646348.PDF\" target=\"new\">平安银行：2013年年度报告摘要</a>";
			preg_match('/href\=\s*[\'\"]?\s*([^"\']+)/' , $tmpStr , $download);
			if(count($download) != 2){
				var_dump($tmpStr);
				Debug::output('download link' , E_ERROR);
			}
			//preg_match('/href\=\"([^\s]*)\"\s+/' , $tmpStr , $download);
			preg_match('/\>\s*\(\s*(\d*)\s*k\s*\)\s*/' , $tmpStr , $size);
			if(count($size) != 2){
				var_dump($tmpStr);
				$size[1] = 0;
			}
			//$tmpStr = "<a href='finalpage/2008-04-22/38959757.PDF' target='new'>*ST宜地：2007年年度报告（补充后）</a>";
			preg_match('/\>\s*([^\>]*)\s*\<\s*\\/a\s*\>/' , $tmpStr , $title);
			if(count($title) != 2){
				var_dump($tmpStr);
				echo "<br/>";
				Debug::output('title is wrong' , E_ERROR);
			} else {
				$title[1] = mb_convert_encoding($title[1] ,'UTF-8', 'CP936');
			}
			$res[] = array(
						$time[1] ,$baseUrl . $download[1] , $size[1] , $title[1],
						$page['notice'] , $page['code'] ,
						$page['q_num'] ,
						strtotime($time[1]),
					);
		}
		return $res;
	}

	/**
	 * 获取最新的数据，并且保存
	 */
	function refresh(){
		//抓取遗漏的数据
		$prefix = array('002' , '200' ,'300' , '900' , '000' ,'301' , '311');
		// 301 , 311 是以后会增加的
		//010301 年度报告
		//010303 半年度报告
		//010305 一季度报告
		//010307 三季度报告
		//$notice = array('010301' , '010303' , '010305' , '010307');
		$notice = array('010301' => 'q4', '010303' => 'q2' , '010305' => 'q1' , '010307' => 'q3');
		$this->DataBaseModel->setTables('data');
		$list = array('time' , 'link' , 'size' , 'title' , 'notice' , 'code' , 'q_num' , 'timestamp');
		for($i = 580 ;$i <= 999 ;$i++){
			foreach($prefix as $pre){
				$stockCode = $this->getStockCode($i, $pre);
				echo $stockCode . "\n";
				foreach ($notice as $key => $value) {
					$args['stockCode'] = $stockCode;
					$args['noticeType'] = $key;
					$args['startTime'] = $this->getTime('-', 1, 1);
					$page = trim($this->getCompanyInfo($args));
					$pageState = $this->checkPageRight($page);
					if($pageState && $pageState['now']){
						$res = $this->getPageRows(
							$page , 
							array('notice' => $key , 'code' => $stockCode , 'q_num' => $value)
						);	
						foreach($res as $row){
							$stored = $this->DataBaseModel->select('*' , array('code' => $stockCode ,'timestamp' => $row[7] ));
							$flag = 0;
							foreach($stored as $old){
								//标题相同，已经插入了
								if($old['title'] === $row[3]){
									$flag = 1;
									break;
								}
							}

							//完全没类似数据
							if($flag === 0){
								$flag = $this->DataBaseModel->insert(
									$list , array($row)
								);	
								if($flag){
									echo "insert success\n";
									var_dump($row);
								}
							}
						}
					}
				}
				flush();
			}
		}
	}
	/**
	 * 在对应的page表里面,解析出来对应的数据
	 *
	 **/
	public function selectPage()
	{
		$this->DataBaseModel->setTables($this->config['shenpage']);
		$data = $this->DataBaseModel->select('q_num, notice , code ,content' , array('notice' => array('010305' ,'010301' , '010303' , '010307') ));
		//$prefix = array('000' , '200' ,'080' ,'031');
		$this->DataBaseModel->createTable('data');
		foreach ($data  as $page ) {
			$res = $this->getPageRows(base64_decode($page['content']) , $page);	
			$list = array('time' , 'link' , 'size' , 'title' , 'notice' , 'code' , 'q_num' , 'timestamp');
			if($this->DataBaseModel->insert($list , $res)){
				echo $page['notice'] . "\n";
				echo $page['code'] . "\n";
				flush();
			}
		}
	}

}
