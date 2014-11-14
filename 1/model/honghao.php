<?php
/*************************************************************************
 * File Name :    ./model/honghao.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 这个项目的根
 **/
class Honghao
{
	
	static $instance;
	protected $token;
	function __construct()
	{
		
		$this->load = new Loader ;
		self::$instance = &$this;
		$this->load->config('db');
		if(!$this->token){
			$this->token = $this->getToken();
		}
	}
	/**
	 * 获取对应的token
	 *
	 * @return string 
	 **/
	public function getToken()
	{
		$this->load->model('BaseModelHttp');
		var_dump($this->config['mx']);
		if(isset($this->config['mx']['appid']) && isset($this->config['mx']['secret'])){
			$json = $this->BaseModelHttp->get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}");
			$json = json_encode($json);
			if(array_key_exists('access_token' , $json)){
				return $json;
			} else if(array_key_exists('errcode' , $json)){
				var_dump($json);
			} else {
				error("未知错误在honghao/" . __LINE __ . "行");
			}
		} else {
			error("没有配置对应的appid和secret");	
		}
	}
}
