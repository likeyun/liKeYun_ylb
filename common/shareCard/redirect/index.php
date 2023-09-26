<html>
    <head>
        <meta name="wechat-enable-text-zoom-em" content="true">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="color-scheme" content="light dark">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <link rel="shortcut icon" type="image/x-icon" href="//res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico" reportloaderror>
        <link rel="mask-icon" href="//res.wx.qq.com/a/wx_fed/assets/res/MjliNWVm.svg" color="#4C4C4C" reportloaderror>
        <link rel="apple-touch-icon-precomposed" href="//res.wx.qq.com/a/wx_fed/assets/res/OTE0YTAw.png" reportloaderror>
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <meta name="referrer" content="origin-when-cross-origin">
        <meta name="referrer" content="strict-origin-when-cross-origin">
        <style>
            *{
                padding:0;
                margin:0;
            }
            #shareGuide{
                width: 100%;
                position: fixed;
                top:0;
            }
            #shareGuide img{
                width: 100%;
            }
            #logo{
                width: 200px;
                position: fixed;
                top: 300px;
                left: 0;
                right: 0;
                margin:0 auto;
                opacity: 0.5;
            }
            #warnning{
                width: 80px;
                height: 80px;
                margin: 50px auto 20px;
            }
            #warnText{
                text-align: center;
                font-size: 20px;
                color: #000;
                font-weight: bold;
            }
            #warnning img{
                width: 80px;
                height: 80px;
            }
        </style>
    </head>

    <body>
        
    <?php
    
    // 获取参数
    $sid = trim(intval($_GET['sid']));
    
    // 过滤参数
    if($sid){
        
        // 数据库配置
        include '../../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取配置
        $get_appid = $db->set_table('huoma_shareCardConfig')->find(['id'=>1]);
        $appid = json_decode(json_encode($get_appid))->appid;
        $appsecret = json_decode(json_encode($get_appid))->appsecret;
        
        // 提醒文字
        function warnInfo($title,$warnText){
            
            return '
            <title>'.$title.'</title>
            <div id="warnning">
                <img src="../../../static/img/warn.png" />
            </div>
            <p id="warnText">'.$warnText.'</p>';
        }
        
        // 根据sid获取shareCardInfo
        $getshareCardInfoResult = $db->set_table('huoma_shareCard')->find(['shareCard_id'=>$sid]);
        if($getshareCardInfoResult){
            
            echo '<title>引流宝分享卡片</title>';
            
            // 落地域名
            $shareCard_ldym = json_decode(json_encode($getshareCardInfoResult))->shareCard_ldym;
            
            // 分享标题
            $shareCard_title = json_decode(json_encode($getshareCardInfoResult))->shareCard_title;
            
            // 分享摘要
            $shareCard_desc = json_decode(json_encode($getshareCardInfoResult))->shareCard_desc;
            
            // 分享缩略图
            $shareCard_img = json_decode(json_encode($getshareCardInfoResult))->shareCard_img;
            
            // 落地页
            $ldymPageUrl = dirname(dirname($shareCard_ldym.$_SERVER['REQUEST_URI'])).'/?sid='.$sid;
            
            // 顶部引导图
            echo '<div id="shareGuide"><img src="../../../static/img/shareGuide.png" /></div>';
            
            // LOLO
            echo '<img src="../../../static/img/20221025100444.png" id="logo" />';
            
            // 请求接口获取新的access_token
            function getNewToken($appid,$appsecret){
                $get_access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret."";
                $access_token_json =  file_get_contents($get_access_token_url);
                $access_token = json_decode($access_token_json)->access_token;
                return $access_token;
            }
            
            // 请求接口获取新的jsapi_ticket
            function getNewTicket($access_token_Str){
                
                $get_jsapi_ticket_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token='.$access_token_Str;
                $jsapi_ticket = file_get_contents($get_jsapi_ticket_url);
                return json_decode($jsapi_ticket)->ticket;
            }
            
            // 从配置中获取access_token
            $get_access_token = $db->set_table('huoma_shareCardConfig')->find(['id'=>1]);
            
            // 判断是否有access_token
            if($get_access_token){
                
                // 获取access_token
                $access_token = json_decode(json_encode($get_access_token))->access_token;
                $access_token_expire_time = json_decode(json_encode($get_access_token))->access_token_expire_time;
                if($access_token){
                    
                    // 有token
                    // 判断有效期
                    if(time() > $access_token_expire_time){
                        
                        // 已过期
                        // 请求接口获取新的access_token
                        $access_token_Str = getNewToken($appid,$appsecret);
                        $NewToken = ['access_token'=>$access_token_Str,'access_token_expire_time'=>time()+7000];
                        $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewToken);
                    }else{
                        
                        // 未过期
                        $access_token_Str = $access_token;
                    }
                }else{
                    
                    // 没有token
                    // 请求接口获取新的access_token
                    $access_token_Str = getNewToken($appid,$appsecret);
                    $NewToken = ['access_token'=>$access_token_Str,'access_token_expire_time'=>time()+7000];
                    $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewToken);
                }
                
                // 获取jsapi_ticket
                // 从配置中获取access_token
                $get_jsapi_ticket = $db->set_table('huoma_shareCardConfig')->find(['id'=>1]);
                $jsapi_ticket = json_decode(json_encode($get_jsapi_ticket))->jsapi_ticket;
                $jsapi_ticket_expire_time = json_decode(json_encode($get_jsapi_ticket))->jsapi_ticket_expire_time;
                if($jsapi_ticket){
                    
                    // 有token
                    // 判断有效期
                    if(time() > $jsapi_ticket_expire_time){
                        
                        // 已过期
                        // 请求接口获取新的jsapi_ticket
                        $jsapi_ticket_Str = getNewTicket($access_token_Str);
                        $NewTicket = ['jsapi_ticket'=>$jsapi_ticket_Str,'jsapi_ticket_expire_time'=>time()+7000];
                        $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewTicket);
                    }else{
                        
                        // 未过期
                        $jsapi_ticket_Str = $jsapi_ticket;
                    }
                }else{
                    
                    // 没有jsapi_ticket
                    // 请求接口获取新的jsapi_ticket
                    $jsapi_ticket_Str = getNewTicket($access_token_Str);
                    $NewTicket = ['jsapi_ticket'=>$jsapi_ticket_Str,'jsapi_ticket_expire_time'=>time()+7000];
                    $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewTicket);
                }
                
                
                // 获取当前页面URL
                $protocol = (
                    !empty($_SERVER['HTTPS']) && 
                    $_SERVER['HTTPS'] !== 'off' || 
                    $_SERVER['SERVER_PORT'] == 443
                ) ? "https://" : "http://";
                $thisPageurl = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                
                // 时间戳
                $timestamp = time();
                
                // 生成nonceStr
                $createNonceStr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                str_shuffle($createNonceStr);
                $nonceStr = substr(str_shuffle($createNonceStr),0,16);
                
                // 按照key值ASCII码升序排序
                $signStringVal = "jsapi_ticket=$jsapi_ticket_Str&noncestr=$nonceStr&timestamp=$timestamp&url=$thisPageurl";
                
                // 按顺序排列按sha1加密生成字符串
                $signature = sha1($signStringVal);
            }
            
        }else{
            
            // 获取失败
            echo warnInfo('提示','页面不存在或已被管理员删除');
            exit;
        }
    }else{
        
        // 参数为空
        echo warnInfo('提示','请求参数为空');
        exit;
    }
    
    ?>
    
    <script src="../../../static/js/jweixin-1.6.0.js"></script>
    <script type="text/javascript">
    
    // 初始化配置
    wx.config({
       debug: false,
       appId: '<?php echo $appid;?>',
       timestamp: '<?php echo $timestamp;?>',
       nonceStr: '<?php echo $nonceStr;?>',
       signature: '<?php echo $signature;?>',
       jsApiList: [
         'updateAppMessageShareData', // 分享到朋友圈
         'updateTimelineShareData',// 分享给朋友
       ]
    });
    
    // 调用ready函数
    wx.ready(function (res) {
        
        //分享到朋友圈
        wx.updateTimelineShareData({
            
            title: '<?php echo $shareCard_title; ?>', // 分享标题
            link: '<?php echo $ldymPageUrl; ?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: '<?php echo $shareCard_img; ?>', // 分享图标
        })
        
        // 分享给朋友
        wx.updateAppMessageShareData({ 
            title: '<?php echo $shareCard_title; ?>', // 分享标题
            desc: '<?php echo $shareCard_desc; ?>', // 分享描述
            link: '<?php echo $ldymPageUrl; ?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: '<?php echo $shareCard_img; ?>', // 分享图标
        })
    });

    // 错误信息
    wx.error(function(res){
        
        // 弹出错误信息以辅助调试
        alert(JSON.stringify(res.errMsg));
        
    });
    
    </script>
    </body>

</html>