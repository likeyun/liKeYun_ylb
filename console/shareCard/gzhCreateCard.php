<?php

    // Token验证
    $token = 'likeyun';
    
    // 开始验证
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce = $_GET["nonce"];
    $echostr = $_GET["echostr"];
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode($tmpArr);
    $tmpStr = sha1($tmpStr);
    
    // 验证通过
    if ($tmpStr == $signature && $echostr) {
        echo $echostr;
        exit;
    }

    // 编码
    header("Content-type:text/html;charset=utf-8");
    
    // 接收用户发送过来的XML消息
    $postStr = file_get_contents('php://input');
    
    // 使用SimpleXML进行解析XML
    $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
    
    // 消息发送方
    $fromUsername = $postObj->FromUserName;
    
    // 公众号
    $toUsername = $postObj->ToUserName;
    
    // 内容
    $content = trim($postObj->Content);
    
    // 消息类型
    $msgType = $postObj->MsgType;
    
    // 时间戳
    $time = time();
  
    // 文本消息XML模板
    $textTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[%s]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    </xml>";
    
    // 图文消息XML模板
    $newsTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[%s]]></MsgType>
    <ArticleCount>1</ArticleCount>
    <Articles>
    <item>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <PicUrl><![CDATA[%s]]></PicUrl>
    <Url><![CDATA[%s]]></Url>
    </item>
    </Articles>
    </xml>";
    
    // 数据库配置
	include '../Db.php';

	// 实例化类
	$db = new DB_API($config);
	
    if (strpos($content, "分享卡片") !== false) {
        
        preg_match('/分享卡片\s*(\d{6})\b/', $content, $matches);
        
        if (isset($matches[1])) {
            
            // 去除参数前后的空格
            $parameter = trim($matches[1]);
            
            // 验证参数是否是6位数的纯数字
            if (strlen($parameter) === 6 && ctype_digit($parameter)) {
                
                // 查询当前输入的id是否存在
                $checkCardIdResult = $db->set_table('huoma_shareCard')->find(['shareCard_id' => $parameter]);
                if($checkCardIdResult){
                    
                    // 存在
                    // 获取标题
                    // 缩略图
                    // 跳转链接
                    // 摘要
                    $getInfo = $db->set_table('huoma_shareCard')->find(['shareCard_id' => $parameter]);
                    
                    // 标题
                    $shareCard_title = $getInfo['shareCard_title'];
                    
                    // 缩略图
                    $shareCard_img = $getInfo['shareCard_img'];
                    
                    // 跳转链接
                    $shareCard_url = $getInfo['shareCard_url'];
                    
                    // 摘要
                    $shareCard_desc = $getInfo['shareCard_desc'];
                    
                    // 构造卡片消息
                    // 并回复卡片
                    echo sprintf($newsTpl,$fromUsername,$toUsername,$time,"news",$shareCard_title,$shareCard_desc,$shareCard_img,$shareCard_url);
                }else{
                    
                    // 回复：该卡片id不存在
                    echo sprintf($textTpl,$fromUsername,$toUsername,$time,"text","该卡片id不存在");
                }
            } else {
                
                // 回复：该卡片id有误
                echo sprintf($textTpl,$fromUsername,$toUsername,$time,"text","该卡片id有误");
            }
        } else {
            
            // 回复：卡片id不正确
            echo sprintf($textTpl,$fromUsername,$toUsername,$time,"text","卡片id不正确");
        }
    } else {
        
        // 回复：该指令无法识别
        echo sprintf($textTpl,$fromUsername,$toUsername,$time,"text","该指令无法识别");
    }
    
?>