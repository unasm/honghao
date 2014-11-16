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
	 * @param	int				$toUser		用户的openId	
	 * @todo 目前只是针对微信端的格式输出
	 */
	function formStr($content , $toUser , $fromUser = '鸿昊投资'){
		$textTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";             
		$resultStr = '';
		if(is_array($content)){
			foreach($content as $data)	{
				$resultStr .= sprintf($textTpl, $fromUser, $toUser, time(), 'text', $data);
			}
		} else {
            $resultStr = sprintf($textTpl, $fromUser, $toUser, time(), 'text', $content);
		}
		echo $resultStr;
	}
}
