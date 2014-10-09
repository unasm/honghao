<?php
/*************************************************************************
 * File Name :    curl.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
class myCurl extends Core{
	//为了维持cookie回话，有必要curl统一这里就没有必要
	private $curl;
	public $token;
	private static $_instance = null;
	//负责初始化token
	function __construct($token = false){
		if(self::$_instance){
			exit("use instance these this aggain , singleton");
		}


		//根据code获取对应的token,之所以放在这里，是为了复用，在非主页的地方使用code ,token
		if($token === false){
			$code = trim($_COOKIE['code']);
			if($code && preg_match("/^[\w\d]+$/" , $code)){
				$saToken = unserialize(file_get_contents(PATH_ROOT . 'token.txt'));
				//var_dump($saToken[$code]);
				if($saToken && array_key_exists($code, $saToken) && (int)$saToken[$code]['outTime'] > time()){
					$this->token = $saToken[$code]['access_token'];
					return;
				}
			}
			$this->token = false;
		}else{
			$this->token = $token;
		}
	}
	/**
	 * 获取对象的实例
	 *
	 * @return object
	 **/
	public static function &instance($token = false)
	{
		if(is_null(self::$_instance)){
			//self::$_instance = true;
			self::$_instance = new myCurl($token);
		}
		return self::$_instance;
	}
	/**
	 * 发送具体的http请求
	 * @param string	$url	curl的请求地址
	 * @param array		$post	post数组
	 * @param boolen	$get	是否是get请求，默认发起post请求
	 * @todo get转化功能
	 */
	public function http($url , $post = array() , $get = false){
		$this->curl = curl_init(); 
		//$headers = array('Accept: application/json','Content-Type: multipart/form-data'); 
		$headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
		curl_setopt($this->curl , CURLOPT_HEADER, 0);
		if($get === false){
			$postData = '';
			foreach($post as $k => $v){
				$postData  .= rawurlencode($k) ."=" . rawurlencode($v) . "&";
			}
			curl_setopt($this->curl , CURLOPT_POSTFIELDS, $postData);
		} else{
			$url .= '?'	;
			foreach($post as $key => $value){
				$url .= urlencode($key) . '=' . urlencode($value) . '&'; 
			}
		}
		//curl_setopt($this->curl , CURLOPT_HTTPHEADER,$headers);
		//curl_setopt($this->curl , CURLOPT_POST ,true);
		curl_setopt($this->curl , CURLOPT_URL, $url);
		curl_setopt($this->curl , CURLOPT_RETURNTRANSFER , 1);

		$data = curl_exec($this->curl);
		$error = curl_error($this->curl);
		if($error){
			echo $error;
		}
		curl_close($this->curl);
		return $data;
	}
	/**
	 * Get the header info to store.
	 *
	 * @return int
	 * @ignore
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
	/**
	 * 官方提供的方法中的curl方案
	 *
	 * @return string curl得到的字符流
	 **/
	function formCurl($url, $postfields = array(),$method = "POST" , $headers = array()) {
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

		$userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
		curl_setopt($ci, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 200);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		if (version_compare(phpversion(), '5.4.0', '<')) {
			curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
		} else {
			curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		}
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					$this->postdata = $postfields;
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

		if ( isset($this->token) && $this->token )
			$headers[] = "Authorization: OAuth2 ".$this->token;

		if ( !empty($this->remote_ip) ) {
			if ( defined('SAE_ACCESSKEY') ) {
				$headers[] = "SaeRemoteIP: " . $this->remote_ip;
			} else {
				$headers[] = "API-RemoteIP: " . $this->remote_ip;
			}
		} else {
			if ( !defined('SAE_ACCESSKEY') ) {
				$headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
			}
		}
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;

		if ($this->debug) {
			echo "=====post data======\r\n";
			var_dump($postfields);

			echo "=====headers======\r\n";
			print_r($headers);

			echo '=====request info====='."\r\n";
			print_r( curl_getinfo($ci) );

			echo '=====response====='."\r\n";
			print_r( $response );
		}
		curl_close ($ci);
		return $response;
	}

	/**
	 * 通过http向具体的url获取数据
	 */
	function getData($url , $param = array(), $get = true){
		$param['access_token'] = $this->token;
		return $this->http($url,$param , $get);
	}	
	/**
	 * 根据传入的参数和url获取对应的token内容
	 *
	 * @return array
	 * @author jiamin1
	 **/
	public function getToken($url , $post)
	{
		$token =  $this->http($url,$post);
		$token = json_decode($token,true);
		if(array_key_exists("access_token" , $token)){
			$this->token = $token['access_token'];
			$UID = $saToken[$code]['uid'];
			//返回数据中expires_in记录了token的保留时间，有效时间
			//uid是当前登录的id
			//access_token是我们想要的token
		}else {
			//var_dump($token);
			error($token['error']);
			return ;
		}
		return $token;
	}
	function __clone(){
		trigger_error("clone is not permit");
	}
}
?>
