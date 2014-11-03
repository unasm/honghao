<?php
/*************************************************************************
 * File Name :    ./shengetcode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
if(!class_exists('Getcode')){
	require 'getcode.php';
}
define("DEBUG" , true);
/**
 * 具体实现深圳股市的实现
 **/
class Ganggetcode extends Getcode
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
	}
	/**
	 * 生成对应的香港股市编号
	 */
	public function makeCode()
	{
	}
	
	/**
	 * 在对应的page表里面,解析出来对应的数据
	 *
	 **/
	public function selectPage()
	{
		$data = 'ds';
		if(DEBUG){
			$data = $this->getCompanyInfo();
			$data = array(file_get_contents(PATH_ROOT . 'game.html' , true));
		}
		/*
		$this->DataBaseModel->setTables($this->config['shenpage']);
		$data = $this->DataBaseModel->select(' notice , code ,content');
		$this->DataBaseModel->createTable('data');
		$cnt = 0;
		 */
		$baseUrl = "http://www.hkexnews.hk/";
		foreach ($data  as $page ) {
			$this->HtmlParserModel->parseStr($page, array(), "BIG5");
			$lines = $this->HtmlParserModel->find('#ctl00_gvMain');
			$this->HtmlParserModel->parseStr($lines[0]->value , array() , 'BIG5');
			$lines = $this->HtmlParserModel->find('tr');
			//从每一行td中获取时间和标题，以及对应的下载连接
			$cnt = 0;
			foreach($lines as $line){
				$cnt++;
				$tmpStr = $line->value;
				//$tmpStr = mb_convert_encoding($tmpStr , 'big5' , 'auto');
				// 匹配时间
				//$tmpStr = "sdfa 12/02/3221 sdf";
				preg_match('/\>\s*(\d{2}\s*\/\s*\d{2}\s*\/\s*\d{4}\s*)\s*\</' , $tmpStr , $time);
				if(count($time) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					Debug::output('time is wrong' , E_NOTICE);
					continue;
				}
				//$tmpStr = "<a href=\"finalpage/2014-03-07/63646348.PDF\" target=\"new\">平安银行：2013年年度报告摘要</a>";
				//匹配下载连接
				preg_match('/href\=\s*[\'\"]?\s*([^"\']+)/' , $tmpStr , $download);
				if(count($download) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					//如果数据不符合，就提示之后跳过
					Debug::output('download link' , E_NOTICE);
					continue;
				}
				//preg_match('/href\=\"([^\s]*)\"\s+/' , $tmpStr , $download);
				//匹配文件大小
				preg_match('/\>\s*\(\s*(\d*)\s*KB,\s*\s*/' , $tmpStr , $size);
				if(count($size) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					Debug::output('size is wrong' , E_NOTICE);
					continue;
				}
				//$tmpStr = "<a href='finalpage/2008-04-22/38959757.PDF' target='new'>*ST宜地：2007年年度报告（补充后）</a>";
				//匹配标题
				preg_match('/\>\s*([^\>]*)\s*\<\s*\\/a\s*\>/' , $tmpStr , $title);
				if(count($title) != 2){
					var_dump($tmpStr);
					echo "<br/>";
					Debug::output('title is wrong' , E_NOTICE);
				}
				echo $cnt . "<br/>";
				flush();
				$this->DataBaseModel->insert(
					array('time' , 'link' , 'size' , 'title' , 'notice' , 'code'), 
					array(
						array(
							$time[1] ,
						   	$baseUrl . $download[1] , 
							$size[1] ,
						   	$title[1] ,
						   	'000' , '000')
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
		//echo "<html><head><meta charset =  'utf-8'></head><body>" . $data[0]['title']. "</body></html>";
		//var_dump($data[0]['title']);
	}
	/**
	 * 根据对应的code获取对应公司的财报，不过这里只是包括了深圳证券交易所的
	 * @param string	$code	上市公司的代码
	 * @param string	$notice	年报的类型
	 **/
	public function getCompanyInfo($code = "000001" , $notice = "010301")
	{
		//$args['stockCode'] = $code;
		//这里的时间将来要修改
		$args['__VIEWSTATE'] = "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";

		$args['ctl00$rdo_SelectSortBy'] = 'rbDateTime';
		$args['ctl00$sel_defaultDateRange'] = "SevenDays";
		$args['ctl00$sel_DateOfReleaseTo_y'] = '2014';
		$args['ctl00$sel_DateOfReleaseTo_m'] = '10';
		$args['ctl00$sel_DateOfReleaseTo_d'] = '28';
		$args['ctl00$sel_DateOfReleaseFrom_y'] = "1999";
		$args['ctl00$sel_DateOfReleaseFrom_m'] = '04';
		$args['ctl00$sel_DateOfReleaseFrom_d'] = '01';
		$args['ctl00$rdo_SelectDateOfRelease'] = 'rbManualRange';
		$args['ctl00$ddlTierTwoGroup']  = '2,1';
		$args['ctl00$ddlTierTwo'] = '59,1,7';
		$args['ctl00$sel_tier_2'] = -2;
		$args['ctl00$sel_tier_2_group'] = -2;
		$args['ctl00$sel_DocTypePrior2006'] = -1;
		$args['ctl00$sel_tier_1'] = -2;
		$args['ctl00$rdo_SelectDocType'] = 'rbAll';
		$args['ctl00$txt_stock_code'] = '00010';
		$args['ctl00$hfAlert'] = '';
		$args['ctl00$hfStatus'] = 'ACM';
		$args['ctl00$txt_today'] = 20141029;
		$args['__VIEWSTATEENCRYPTED'] = '';
		$header = array(
			"Host: www.hkexnews.hk" ,
			"Referer: http://www.hkexnews.hk/listedco/listconews/advancedsearch/search_active_main_c.aspx"	, 
			"Origin: http://www.hkexnews.hk" , 
			"User-Agent: Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/37.0.2062.120 Chrome/37.0.2062.120 Safari/537.36" , 
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8" ,
			"Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.6,en;q=0.4"
		);
		$cookie = "TS0161f2e5=0141259276c4ffb01f569f60012faeb010e26ca336004ee02e58653fdc2b56e750684b5c2b";
		$page =  $this->BaseModelHttp->post("http://www.hkexnews.hk/listedco/listconews/advancedsearch/search_active_main_c.aspx" , $args, $header , 200 , $cookie);
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
