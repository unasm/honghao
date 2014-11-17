<?php
/*************************************************************************
 * File Name :    ../model/output.php
 * Author    :    jiamin1
 * Mail      :    jiamin1@staff.sina.com.cn
 ************************************************************************/
/**
 * 对外显示数据，为了应对微信端和web端以及终端的情况
 */
class output {
	/**
	 * 整理成特定的字符串，输出
	 * @param	array/string	$content	想要输入的内容
	 * @param	object			$obj		the code parsed when input
	 * @todo 目前只是针对微信端的格式输出
	 */
	function formStr($content , $obj){
		$textTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";             
		$resultStr = '';
		if(is_array($content)){
			foreach($content as $data)	{
				$resultStr .= sprintf($textTpl, $obj->FromUserName, $obj->ToUserName, time() , $data);
			}
		} else {
            $resultStr = sprintf($textTpl, $fromUser, $toUser, time(), $content);
		}
		echo $resultStr;
	}
	/**
	 * 测试微信输出
	 *
	 * @return void
	 * @author Me
	 **/
	public function test($text , $from , $to)
	{
		$test = "<xml>
			<ToUserName><![CDATA[{$from}]]></ToUserName>
			<FromUserName><![CDATA[{$to}]]></FromUserName>
			<CreateTime>12345678</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[你好]]></Content>
			</xml>";
		echo  $test;
	}
}
