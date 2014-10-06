<?php
/*************************************************************************
 * File Name :    ./getCode.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 本文件主要是用来获取股票交易码的
 */
require "./index.php";
require BasePath . "model/HtmlParserModel.php";
require BasePath . "model/BaseModelHttp.php";
//命令行的时候，读取配置路由内容
class shenZhen{
	static $shenCode;
	static $shangCode;
	public function __construct()
	{
		$this->shenCode = array();
		$this->shangCode = array();
	}
	/**
	 * 从$url中获取对应的股票交易码,不过只有上证和深证的
	 */
	function getCode(){
		$code = array();
		$url = "http://www.dqw.cn/007gupiao/zonghe/daima.htm";
		$fileName = basename($url);
		$data = "";
		if(file_exists(BasePath . 'cache/' . $fileName)){
			$data = file_get_contents(BasePath . 'cache/' . $fileName ,true)	;
		}else{
			$data = BaseModelHttp::curl(array("url" => $url));
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
		/**
			for($j = 0,$slen = count($result) ; $j < $slen;$j++){
				echo $result[$j]['code'] . "    ";
			}
			continue;
		if(count($result) === 0){
			echo $comName[$key] . "\n<br/>";
		}else{
			$fileName = BasePath . 'cache/' . $comName[$key] . '.txt';
			//如果不存在，或者是存在可写
			if(is_writeable($fileName) || !file_exists($fileName)){
				$fp = fopen($fileName , 'w') or die("无法打开{$fileName}文件\n<br/>");
				$state = fwrite($fp , json_encode($result));
				if($state === false){
					echo $key . "\n";
					//var_dump($result);
					//exit("没有写入文件");
				}else{
					echo "yes";
				}
				fclose($fp);
			}else{
				echo "存在不可写\n";
			}
		}
		 */
			//$code[$key] = $result ;
			if($key === 0 || $key === 4){
				$this->shangCode[] = $result;
			}else{
				$this->shenCode[] = $result;
			}
		}
	}
	/**
	 * 根据对应的code获取对应公司的财报，不过这里只是包括了深圳证券交易所的
	 **/
	public function getCompanyInfo($code = "000001")
	{
		$args['stockCode'] = $code;
		$args['keyword'] = "";
		$args['noticeType'] = "010301";
		$args['startTime'] = "2002-10-02";
		$args['endTime'] = "2014-10-05";
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
		$page =  BaseModelHttp::post("http://disclosure.szse.cn/m/search0425.jsp" , $args, $header , 200 , $cookie);
		echo $page;
		//echo strlen($page);
		//echo BaseModelHttp::post("http://127.0.0.4:8080/test.php" , $args, $header , 200 , $cookie);
	}
	//用来测试验证是否可以通过那些code数据来大规模获取对应的年报
	public function getAllShenCode()
	{
		$this->getCode();
		foreach($this->shenCode as $codes){
			//var_dump($codes);
			$i = 2;
			$this->getCompanyInfo($codes[$i]['code']);
			return;
			for($i = 8, $len = count($codes);$i < $len && $i < 10;$i++){
				//echo $codes[$i]['code'] . "\n";
				$this->getCompanyInfo($codes[$i]['code']);
				echo "\n";
			}
			break;
		}
	}
}
$shen = new shenZhen();
$shen->getAllShenCode();
?>
