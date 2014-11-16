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
		/*
		if(!$this->token){
			$this->token = $this->getToken();
		}
		 */
	}
	/**
	 * 获取对应的token
	 *
	 * @return string 
	 **/
	protected function httpGetToken()
	{
		$this->load->model('BaseModelHttp');
		if(isset($this->config['wx']['appid']) && isset($this->config['wx']['secret'])){
			$json = $this->BaseModelHttp->get(
				"https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->config['wx']['appid']}&secret={$this->config['wx']['secret']}"
			);
			$json = json_decode($json , true);
			if(array_key_exists('access_token' , $json)){
				return $json;
			} else if(array_key_exists('errcode' , $json)){
				var_dump($json);
				die;
			} else {
				error("未知错误在honghao/" . __LINE__ . "行");
			}
		} else {
			error("没有配置对应的appid和secret");	
		}
	}

	/**
	 * 判断是否需要获取token
	 *
	 **/
	public function getToken()
	{
		$token = $this->getCache('token');
		$flag = 0;
		if($token ){
			$token = json_decode($token , true);
			if(time() < $token['expires_in'] ){
				$flag = 1;
			}
		}
		if($flag){
			return $token['access_token'];
		} else {
			$arr = $this->httpGetToken();
			$arr['expires_in'] = $arr['expires_in'] + time();
			$this->setCache('token' , json_encode($arr));
			return $arr['access_token'];
		}
	}
}
