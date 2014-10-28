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
			} else {
				ob_clean();
				var_dump($page);
				Debug::output('Shengetcode/' . __LINE__ . '出现了count  != 2的情况' , E_ERROR);
			}
		}else{
			ob_clean();
			var_dump($page);
			Debug::output('shenggetcode/' . __LINE__ . '出现了>1的错误'  , E_ERROR);
			ob_end_flush();
		}
		return false;
	}
	/**
	 * 生成深圳上市公司的代码
	 * 生存代码，并抓取对应的页面，如果有多个页面，一并抓取，是为了不再定向查找，方便接下来解析所有的页面;
	 */
	public function makeCode()
	{
		ob_start();
		//深市A股，B股,配股
		//$this->DataBaseModel->createTable('code');
		//上市公司的前缀
		$prefix = array('000' , '200' ,'080' ,'031');

		//010301 年度报告
		//010303 半年度报告
		//010305 一季度报告
		//010307 三季度报告
		//$notice = array('010301' , '010303' , '010305' , '010307');
		$notice = array('010303' , '010305' , '010307');

		$this->DataBaseModel->createTable($this->config['shenpage']);
		for($i = 0;$i <= 999 ;$i++){
			foreach ($notice as $note) {
				$this->createCode($prefix , $i , $note);
			}
		}
		ob_end_flush();
	}
	
	/**
	 * 在对应的page表里面,解析出来对应的数据
	 *
	 **/
	public function selectPage()
	{
		//header("Content-type:text/html;charset=gb2312");
		$this->DataBaseModel->setTables($this->config['shenpage']);
		$data = $this->DataBaseModel->select(' notice , code ,content');
		$this->DataBaseModel->createTable('data');
		$baseUrl = "http://disclosure.szse.cn/";
		$cnt = 0;
		//$last = 10000000;
		foreach ($data  as $page ) {
			$this->HtmlParserModel->parseStr($page['content'], array() , "big5");
			$lines = $this->HtmlParserModel->find('.td2');
			//从每一行td2中获取时间和标题，以及对应的下载连接
			foreach($lines as $line){
				$cnt++;
				$tmpStr = $line->value;

				//$tmpStr = mb_convert_encoding($tmpStr , 'big5' , 'auto');
				// <span class="link1">[2014-10-24]</span>
				// 匹配时间
				preg_match('/\>\[(\d{4}-\d{2}-\d{2})\]/' , $tmpStr , $time);
				if(count($time) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					Debug::output('time is wrong' , E_ERROR);
				}
				//$tmpStr = "<a href=\"finalpage/2014-03-07/63646348.PDF\" target=\"new\">平安银行：2013年年度报告摘要</a>";
				//匹配下载连接
				preg_match('/href\=\s*[\'\"]?\s*([^"\']+)/' , $tmpStr , $download);
				if(count($download) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					Debug::output('download link' , E_ERROR);
				}
				//preg_match('/href\=\"([^\s]*)\"\s+/' , $tmpStr , $download);
				//匹配文件大小
				preg_match('/\>\s*\(\s*(\d*)\s*k\s*\)\s*/' , $tmpStr , $size);
				if(count($size) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					Debug::output('size is wrong' , E_ERROR);
				}
				//$tmpStr = "<a href='finalpage/2008-04-22/38959757.PDF' target='new'>*ST宜地：2007年年度报告（补充后）</a>";
				//匹配标题
				preg_match('/\>\s*([^\>]*)\s*\<\s*\\/a\s*\>/' , $tmpStr , $title);
				if(count($title) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					Debug::output('title is wrong' , E_ERROR);
				}
				echo $cnt . "<br/>";
				var_dump($title[1]);
				flush();
				//die("yes");
				//var_dump($page['content']);
				//var_dump($tmpStr);
				//flush();
				$this->DataBaseModel->insert(
					array('time' , 'link' , 'size' , 'title' , 'notice' , 'code'), 
					array(
						array(
							$time[1] ,
						   	$baseUrl . $download[1] , 
							$size[1] ,
						   	$title[1] ,
						   	$page['notice'] , $page['code'])
					)
				);
			}
		}
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
		echo "<html><head><meta charset =  'utf-8'></head><body>" . $data[0]['title']. "</body></html>";
		//var_dump($data[0]['title']);
	}
	/**
	 * 根据对应的code获取对应公司的财报，不过这里只是包括了深圳证券交易所的
	 * @param string	$code	上市公司的代码
	 * @param string	$notice	年报的类型
	 **/
	public function getCompanyInfo($code = "000001" , $notice = "010301")
	{
		$args['stockCode'] = $code;
		$args['keyword'] = "";
		$args['noticeType'] = "010301";
		$args['startTime'] = "2001-01-01";
		//这里的时间将来要修改
		$args['endTime'] = "2014-10-25";
		$args['imageField'] = array("x"  => 0 , 'y' => 97);
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
		$page =  $this->BaseModelHttp->post("http://disclosure.szse.cn/m/search0425.jsp" , $args, $header , 200 , $cookie);
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


}
