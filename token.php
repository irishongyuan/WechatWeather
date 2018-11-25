<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "haha");
$nonce = $_GET['nonce'];
$token = 'haha';
$timestamp = $_GET['timestamp'];
$echostr   = $_GET['echostr'];
$signature = $_GET['signature'];
//形成数组，然后按字典序排序
$array = array();
$array = array($nonce, $timestamp, $token);
sort($array);
//拼接成字符串,sha1加密 ，然后与signature进行校验
 $str = sha1( implode( $array ) );
if( $str == $signature && $echostr ){
	//第一次接入weixin api接口的时候
	echo  $echostr;
	exit;
}else{
	$wechatObj = new wechatCallbackapiTest();
	$wechatObj->responseMsg();
	//$wechatObj->valid();
}

class wechatCallbackapiTest
{
    /*public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }*/

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

          //extract post data
        if (!empty($postStr)){
                
                  $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);

                switch($RX_TYPE)
                {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    public function handleText($postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";             
        if(!empty( $keyword ))
        {
            $msgType = "text";

            //天气
            $str = mb_substr($keyword,-2,2,"UTF-8");
            $str_key = mb_substr($keyword,0,-2,"UTF-8");
            if($str == '天气' && !empty($str_key)){
                $data = $this->weather($str_key);
                if(empty($data->weatherinfo)){
                    $contentStr = "抱歉，没有查到\"".$str_key."\"的天气信息！";
                } else {
                    $contentStr = "【".$data->weatherinfo->city."天气预报】\n".$data->weatherinfo->time."\n\n实时天气\n"."\n当前温度:\n".$data->weatherinfo->temp."\n当前风向风力:\n".$data->weatherinfo->WD." ".$data->weatherinfo->WS."\n\n温馨提示：".$data->weatherinfo->njd;
                }
            } else {
                $contentStr = "感谢您关注【小源不圆】"."\n"."微信号：hongyuaniris"."\n"."查阅北京、上海、苏州天气的微信平台。"."\n"."目前平台功能如下："."\n"."【1】 查天气，如输入：北京天气"."\n"."更多内容，敬请期待...";
            }
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{
            echo "Input something...";
        }
    }

    public function handleEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "感谢您关注【小源不圆】"."\n"."微信号：hongyuaniris"."\n"."查阅北京、上海、苏州天气的微信平台。"."\n"."目前平台功能如下："."\n"."【1】 查天气，如输入：北京天气"."\n"."更多内容，敬请期待...";
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    private function weather($n){
        include("weather_cityId.php");
        $c_name=$weather_cityId[$n];
        if(!empty($c_name)){
            $json=file_get_contents("http://www.weather.com.cn/data/sk/".$c_name.".html");
            return json_decode($json);
        } else {
            return null;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>