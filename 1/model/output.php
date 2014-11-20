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
		if(empty($content) || !$content){
			$content = "没有您想要的财报";		
		} 
		if (is_array($content)){
			foreach($content as $data)	{
				$resultStr .= sprintf($textTpl, $obj->FromUserName, 
					$obj->ToUserName, time() , $data);
			}
		} else {
			$resultStr = sprintf($textTpl, $obj->FromUserName, 
				$obj->ToUserName, time(), $content);
		}
		echo $resultStr;
	}
	/**
	 * 输出包含图文的信息
	 * @param	array	$arr	包含想要数的内容的数组
	 * @param	object	$obj	解析来的对象
	 * @example 			
	 * $arr = array(
				array(
					'title' => "这里是图文测试",
					'desc' => "hello,world",
					'pic' => 'http://img.ycwb.com/ent/attachement/jpg/site2/20131009/6cf0490dd6c713bf01d55c.jpg', 
					'link' => 'http://disclosure.szse.cn/finalpage/2002-04-18/573256.PDF',
				),
				array(
					'title' => "hi, here is tianyi speaking",
					'desc' => "这里是文字的描述",
					'pic' => 'http://img.ycwb.com/ent/attachement/jpg/site2/20131009/6cf0490dd6c713bf01d55c.jpg', 
					'link' => 'http://www.honghaotouzi.sinaapp.com/index.php/home/show',

					//'link' => 'http://mp.weixin.qq.com/mp/redirect?url=http://disclosure.szse.cn/finalpage/2002-04-18/573256.PDF#mp.weixin.qq.com'
				),
	 */
	function PicArticle($arr , $obj){
		$time = time();
		$len = count($arr);
		$textTpl = "<xml>
			<ToUserName><![CDATA[{$obj->FromUserName}]]></ToUserName>
				<FromUserName><![CDATA[{$obj->ToUserName}]]></FromUserName>
				<CreateTime>{$time}</CreateTime>
				<MsgType><![CDATA[news]]></MsgType>	
				<ArticleCount>{$len}</ArticleCount>
				<Articles>";
				foreach($arr as $article){
					$textTpl .= "<item>
						<Title><![CDATA[{$article['title']}]]></Title> 
						<Description><![CDATA[{$article['desc']}]]></Description>
						<PicUrl><![CDATA[{$article['pic']}]]></PicUrl>
						<Url><![CDATA[{$article['link']}]]></Url>
					</item>";
				}
	$textTpl .= "</Articles>
			</xml> ";
		echo $textTpl;
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
