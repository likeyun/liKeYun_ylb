<html>
    <head>
        <meta name="wechat-enable-text-zoom-em" content="true">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="color-scheme" content="light dark">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <link rel="shortcut icon" href="https://res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico">
        <style>
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
        $qid = trim(intval($_GET['qid']));
        
        // 过滤参数
        if($qid){
            
            // 数据库配置
            include '../../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);
            
            // 根据qid获取落地域名
            $getQunldymResult = $db->set_table('huoma_qun')->find(['qun_id'=>$qid]);
            if($getQunldymResult){
                
                // 获取成功
                $qun_ldym = json_decode(json_encode($getQunldymResult))->qun_ldym;
                
                // 获取域名检测配置
                $getDomainNameCheckConfig = $db->set_table('huoma_domainCheck')->find(['id'=>1]);
                if($getDomainNameCheckConfig){
                    
                    // 状态
                    $domainCheck_status = json_decode(json_encode($getDomainNameCheckConfig))->domainCheck_status;
                    
                    // 通知渠道
                    $domainCheck_channel = json_decode(json_encode($getDomainNameCheckConfig))->domainCheck_channel;
                    
                    // 备用域名
                    $domainCheck_byym = json_decode(json_encode($getDomainNameCheckConfig))->domainCheck_byym;
                    
                    if($domainCheck_status == 1){
                        
                        // 开启
                        // 检测域名是否正常
                        // 获取HTTP协议
                        $httpOrhttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                        
                        // 检测接口
                        $getThisPagePath = dirname(dirname(dirname(dirname($_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]))));
                        $checkDomainURL = $httpOrhttps.$getThisPagePath.'/console/public/domainNameCheck.php?domain='.$qun_ldym;
                        
                        // 执行检测
                        $checkResult = file_get_contents($checkDomainURL);
                        
                        // 返回码
                        $checkCode = json_decode($checkResult,true)['code'];
                        
                        // 判断检测结果
                        if($checkCode == 200){
                            
                            // 正常
                            jump($qun_ldym,$qid);
                        }else{
                            
                            // 不正常
                            // 发送通知
                            sendNotification($domainCheck_channel,'群活码'.$qun_ldym.'域名被封了！尽快处理！',$db);
                            
                            // 是否有备用域名
                            if($domainCheck_byym){
                                
                                // 使用备用域名跳转
                                jump($domainCheck_byym,$qid);
                            }else{
                                
                                // 没有
                                // 获取失败
                                echo warnInfo('温馨提示','无法正常跳转或展示');
                            }
                        }
                    }else{
                        
                        // 关闭
                        jump($qun_ldym,$qid);
                    }
                }
            }else{
                
                // 获取失败
                echo warnInfo('温馨提示','该群不存在或已被管理员删除');
            }
        }else{
            
            // 参数为空
            echo warnInfo('温馨提示','请求参数为空'.$qid);
        }
        
        // 跳转
        function jump($qun_ldym,$qid){
            
            // 拼接落地页链接
            $longUrl = dirname(dirname($qun_ldym.$_SERVER['REQUEST_URI'])).'/?qid='.$qid;
            
            // 301跳转
            header('HTTP/1.1 301 Moved Permanently');
            
            // 跳转
            header('Location:'.$longUrl);
        }
        
        // 发送通知
        function sendNotification($noti_type,$noti_text,$db){
            
            // 根据noti_type选择发送的渠道
            include_once '../../../console/public/sendNotification.php';
        }
        
        // 解析数组
        function getSqlData($result,$field){
            
            // 传入数组和需要解析的字段
            return json_decode(json_encode($result))->$field;
        }
        
        // 提醒文字
        function warnInfo($title,$warnText){
            
            return '
            <title>'.$title.'</title>
            <div id="warnning">
                <img src="../../../static/img/warn.png" />
            </div>
            <p id="warnText">'.$warnText.'</p>';
        }
    
    ?>
    </body>
</html>