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
            .example-page {
                width: 80%;
                padding: 20px;
                margin: 30px auto 0;
                background: #eee;
            }
        </style>
    </head>

    <?php
    
        // 接收SID
        $sid = trim(intval($_GET['sid']));
        
        // 通过SID解析出标题、摘要、缩略图
        if($sid){
            
            // 引入SDK
            include 'rzhSDK.php';
            
            // 更新访问次数
            updatePV($db,$sid);
            
            // 记录今天ip访问量
            updateTodayIpNum($db);
            
            // 更新当前小时的总访问量
            updateCurrentHourPageView($db,'shareCard');
        }else{
            
            // 请求参数为空
            echo warnInfo('提示','请求参数为空');
            exit;
        }
    
    ?>
    
    <body>
    
        <!--你可以在这个区域编写你的页面-->
        
        <title>这是一个示例页面</title>
        <div class="example-page">
            这是一个示例页面，点击右上角【···】分享出去的卡片，再点击卡片进去，仍然可以继续分享成卡片，这就是认证号的优点！你可以自己开发属于自己的页面~认证号适合自己开发或搭建的网站。
        </div>
        
        <!--你可以在这个区域编写你的页面-->
        
        <script src="../../static/js/jweixin-1.6.0.js"></script>
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
                    
                    title: '<?php echo $shareCard_title; ?>', // 自动获取当前页面标题
                    link: '<?php echo $thisPageurl; ?>', // 自动获取当前页面Url
                    imgUrl: '<?php echo $shareCard_img; ?>', // 自动获取当前页面图标
                })
                
                // 分享给朋友
                wx.updateAppMessageShareData({ 
                    title: '<?php echo $shareCard_title; ?>', // 自动获取当前页面标题
                    desc: '<?php echo $shareCard_desc; ?>', // 自动获取当前页面描述
                    link: '<?php echo $thisPageurl; ?>', // 自动获取当前页面Url
                    imgUrl: '<?php echo $shareCard_img; ?>', // 自动获取当前页面图标
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