<?php

    // 页面编码
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
	
	// 查询当前输入的id时候存在
    $checkCardId = ['shareCard_id' => $content];
    $checkCardIdResult = $db->set_table('huoma_shareCard')->find($checkCardId);
    
    if($checkCardIdResult){
        
        // 存在
        // 获取标题、缩略图、跳转链接、描述文字
        $getshareCardInfo = $db->set_table('huoma_shareCard')->find(['shareCard_id' => $content]);
        
        // 标题
        $shareCard_title = json_decode(json_encode($getshareCardInfo))->shareCard_title;
        
        // 缩略图
        $shareCard_img = json_decode(json_encode($getshareCardInfo))->shareCard_img;
        
        // 跳转链接
        $shareCard_url = json_decode(json_encode($getshareCardInfo))->shareCard_url;
        
        // 描述文字
        $shareCard_desc = json_decode(json_encode($getshareCardInfo))->shareCard_desc;
        
        // 构造卡片消息
        echo sprintf($newsTpl,$fromUsername,$toUsername,$time,"news",$shareCard_title,$shareCard_desc,$shareCard_img,$shareCard_url);
    }else{
        
        // ID不存在
        echo sprintf($textTpl,$fromUsername,$toUsername,$time,"text","该卡片id不存在");
    }
    
?>