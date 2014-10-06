<?php
/**
 *  通过curl的方式获取网页上面的图片,
 *  应该通过cli的方式执行本文件
 *    @name:       image.php
 *    @author:     unasm<1264310280@qq.com>
 *    @since :     2014-07-19 00:14:29
 *    @todo 进行整站的扫描，从index页面进行开始，
 *    @todo 练习php中的C扩展，保存已经获取过的链接不再进行解析
 *    @todo curl多个进程和单个进程的阻塞
 */
class Image {

    //最小允许的图片大小
    const SIZE = 200;


    //网站的根目录
    const BASE = "http://ac.qq.com/naruto/";

    //重新获取数据的次数
    const reGet = 3;
    var $imgPath ;
    // image 保存路径
    /**
     * 根据指定的url获取对应的图片
     * @todo 通过添加多个curl进行图片的获取
     */
    protected function getData($url){
        $curl = curl_init();
        curl_setopt($curl , CURLOPT_URL, $url);
        $this->curlOptSet($curl);
        ob_start();
        curl_exec($curl);
        $opt = false;
        if(curl_errno($curl)){
            echo "curl error: " . curl_error($curl);
        }else{
            $opt = ob_get_contents();
        }
        ob_end_clean();
        curl_close($curl);
        return $opt;
    }

        /**
         * 对curl进行初始化
         *
         * @return void
         */
        protected function curlOptSet (&$curl)
        {
            static $header = array();
            $header[] = 'Connection: Keep-Alive';
            $header[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
            curl_setopt($curl , CURLOPT_RETURNTRANSFER , true);
            curl_setopt($curl , CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl , CURLOPT_CUSTOMREQUEST,'GET');

            $userAgent ='Mozilla/5.0 (X11; Linux x86_64) AppleWebit/537.36 ()HTML, like Gecko) Ubuntu Chromium/34.0.1847.116 Chrome/34.0.1847.116 Safari/537.36';
            curl_setopt($curl , CURLOPT_USERAGENT , $userAgent);
            curl_setopt($curl , CURLOPT_HTTPHEADER , $header);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        }
        /**
         * @param binary    $data  要保存的图片二进制数据
         * @param string    $name   图片的名字
         */
        public function saveImg($data , $name){
            if(preg_match('/.*\<\/html\>$/' , $data)){
                echo "非img文件,html文件\n";
                echo $data . "<br/>";
                return false;
            }
			if($name[0] != "/"){
				$name = $this->imgPath . $name ;
			}
            if($data){
				echo "saving\n";
                $file = fopen($name , "w") or die("can't open file");
                fwrite($file, $data);
                fclose($file);
                $this->checkAfterDownLoad($name);
                return true;
            }
            return false;
        }
        /**
         * 根据对应的url获取图片
         * @todo 增加检验，和重新获取的功能
         */
        public function getImage($URL , $name = -1){
            if($name === -1){
                $name = basename($URL);
            }
            $name = $this->imgPath . $name ;
            if(file_exists($name)){
                echo "url为:{$URL}且name为{$name}已经存在了";
                return;
            }
            //如果得到了html文档，则不再保存，返回错误
            for($i = 0;$i < self::reGet;$i++){
                $data = trim($this->getData($URL , $name));
                if($data && $this->saveImg($data , $name)){
                    return true;
                }
            }
            return false;
        }
        /**
         * 在下载完毕一个图片之后，检验图片是不是符合我们的规格要求，以免错下
         *
         */
        protected  function checkAfterDownLoad ($name)
        {
            $info = getimagesize($name);
			var_dump($info);
			return true;
            if($info && $info[1] > self::SIZE && $info[0] > self::SIZE){
				echo "image file\n";
                return true;
            }else{
				echo "not image file\n";
                if(unlink($name)){
                    return false;
                }else{
                    throw new Exception("无法删除不合适的下载文件" . $name);
                }
            }
        }
        /**
         * 从 字符/dom 流中读取图片img的地址
         *
         * @param   string  $str    包含了image信息的dom str
         * @return array;
         */
        protected  function getImgUrl ($domStr)
        {
            preg_match_all("/\<img[^>]+src\s*\=\s*[\'|\"]([^\'\"\>]+)[\'|\"][^>]*\>/",$domStr , $matches);
            $imgs =  $this->checkImgUrl($matches[1]);
            //多线程下载
            $this->curl_multi($imgs);
            //单线程下载
            /*
            foreach($imgs as $img){
                if(!$this->getImage($img)){
                    //error("{$img}没有保存成功");
                    echo ("{$img}没有保存成功");
                }
            }
             */
        }
    /**
     * 检验图片before Download
     */
    protected function checkImgUrl($arr , $prefix = "jpg")
    {
        $ret = array();
        foreach($arr as $img){

            $pfx = substr($img ,strlen($img) - 3);
            if($pfx !== $prefix)continue;
            if(substr($img , 0, 4) === "http"){
                $ret[]= $img;
            }else if(substr($img, 0,2) === './'){
            //有可能是相对路径，虽然这个案例中是不应该的，但是考虑到实用性，应该添加相对路径
            $ret[] = self::BASE . $img;
            }
        }
        return $ret;
    }

    /**
     * 检查href是不是曾经搜索过
     *
     * @return boolen
     * @todo 通过其他的算法优化性能
     */
    protected   function isHrefBeen($href)
    {
        static $been = array();
        foreach($been as $url){
            if($url === $href)return false;
        }
        $been[] = $href;
        //echo "没有到过";
        return true;
    }
    /**
     * 根据根据对应的url获取并解析dom，提取其中的a link ,根据其中的base 和是否曾经检验过来作为是否进入并且抓取的依据
     * @param string $url ,需要进入，并且进行解析得到子url的url
     * @param int    $deep 搜索的深度，以免发生无法控制的事情
     * @return array
     */
    public  function getHtmlByUrl($url , $deep)
    {
        if(!$deep)return ;
		$data = $this->getData($url);
        //$data = file_get_contents($url ,FILE_USE_INCLUDE_PATH);
        //获取其中的图片
        //$this->getImgUrl($data);
        //获取其中的链接，递归一次
        preg_match_all("/\<a\s+href\s*\=\s*[\'|\"]([^\'\"\>]+)[\'|\"][^>]*\>/",$data, $matches);
        $len = strlen(self::BASE);
        //$toSea = array();
        //只保留base站以内的链接
        foreach($matches[1] as $href){
            if(substr($href , 0 ,7) != 'http://'){
                //检验是不是相对路径
                $href = self::BASE .  $href;
            }else if(!$this->checkHref($href , $len)){
                //不是相对路径情况下，站外href不考虑
                continue;
            }
			if($this->isHrefBeen($href)){
				$this->getHtmlByUrl($href , $deep -1);
				echo $href . "   判断为未曾进入的url\n";
			}
        }
    }

    /**
     * 检验是不是想要的站内链接
     *
     * @return boolen
     */
    protected function checkHref ($href , $len)
    {
        //其实这样不一定合适，因为二级域名也可以啊
        if(substr($href , 0 , $len) === self::BASE){
            //echo "相同域名";
            return true;
        }
        return false;
    }

    /**
     * curl的php.net样本拷贝
     */
    protected function curl_multi($urls)
    {
        $mh = curl_multi_init();
        // set URL and other appropriate options
		$len = min(10,count($urls));
        for($i = 0;$i < $len ;$i++){
            $ch[$i] = curl_init();
            curl_setopt($ch[$i], CURLOPT_URL,$urls[$i]);
            $this->curlOptSet($ch[$i]);
            curl_multi_add_handle($mh,$ch[$i]);
        }
        //create the multiple cURL handle

        //add the two handles
        $active = null;
        //execute the handles
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM || $active);
        while ($active && $mrc == CURLM_OK) {
			if(curl_multi_select($mh) != -1){
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
        }
            //close the handles
		$startMem = memory_get_usage();
        for($i = 0;$i < $len;$i++){
			if(curl_errno($ch[$i]) === 0){
				$data = curl_multi_getcontent($ch[$i]);
				$this->saveImg($data ,$this->imgPath .  basename($urls[$i]));
				curl_multi_remove_handle($mh, $ch[$i]);
			}else{
				echo "\n错误码:" . curl_errno($ch[$i]);
			}
			$tmp = memory_get_usage();
            curl_close($ch[$i]);
            echo $i . "  use {$tmp}bit\n";
        }
        curl_multi_close($mh);
    }
    /**
     * 设置一些初始化变量
     *
     */
    protected function init()
    {
        $this->imgPath = rtrim(dirname(__FILE__)  , '/') . "/image/";
    }
    function __construct()
    {
        $this->init();
        //$this->getHtmlByUrl("http://www.sina.com.cn" , 2);
        //$this->getHtmlByUrl("http://www.66langke.com/se/chengrenkatong/451449.html" , 2);
        //$flag = $this->getHtmlByUrl("http://www.caoqun5566.com/bbs/", 2);
        //$arr = array("http://ui.narutom.com/images/cartoon/op_class.jpg", "http://ui.narutom.com/images/cartoon/lalala.jpg");
        //$this->curl_multi($arr);
    }
}
    //$image = new Image();
?>
