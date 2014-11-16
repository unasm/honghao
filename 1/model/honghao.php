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
	public function token()
	{
		if(extension_loaded('memcached')){
			$mc = new cache();
			if(!$mc->addServer($this->config['mc']['host'] , $this->config['mc']['port'])){
				error("连接mc失败")	;
			}
			$mc->set('test' , 'hello,world!');
			//sleep(10);
			var_dump($mc->get('test'));			
			$token = $mc->get('token');
			return;
			if($token){
				var_dump($token);
				die;
				return $token;
			} else {
				$arr = $this->httpGetToken();
				echo $arr['access_token'] . "<br/>";
				echo $arr['expires_in'] + time();
				die;
				if(!$mc->set('token' , $arr['access_token'] , time() + $arr['expires_in'] )){
					error("更新mc token失败")	;
				}
				return $arr['access_token'];
			}
		} else {
			error("please install memcached")	;
		}
	}
}
