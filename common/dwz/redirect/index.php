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
        <link rel="stylesheet" href="../../static/css/common.css">
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
    
        // 页面编码
        header("Content-type:text/html;charset=utf-8");
        
        // 获取参数
        $key = trim($_GET['key']);
        
        // 防SQL注入
        if(preg_match('/[_\-\/\[\].,:;\'"=+*`~!@#$%^&()]/',$key)){
           
            echo warnInfo('温馨提示','该链接不安全，请重新生成！');
            exit;
        }
        
        if(preg_match('/(select|update|drop|DROP|insert|create|delete|where|join|script)/i',$key)){
           
            echo warnInfo('温馨提示','该链接不安全，请重新生成！');
            exit;
        }
        
        // 过滤参数
        if($key){
            
            // 数据库配置
            include '../../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);
            
            // 根据key获取落地域名
            // 落地域名 = 中转域名
            $getDwzInfo = $db->set_table('huoma_dwz')->find(['dwz_key'=>$key]);
            
            if($getDwzInfo){
                
                // 中转域名
                $dwz_zzym = json_decode(json_encode($getDwzInfo))->dwz_zzym;
                
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
                        $httpOrhttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        
                        // 检测接口
                        $getThisPagePath = dirname(dirname(dirname(dirname($_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]))));
                        $checkDomainURL = $httpOrhttps.'://'.$getThisPagePath.'/console/public/domainNameCheck.php?domain='.$dwz_zzym;
                        
                        // 执行检测
                        $checkResult = file_get_contents($checkDomainURL);
                        
                        // 返回码
                        $checkCode = json_decode($checkResult,true)['code'];
                        
                        // 判断检测结果
                        if($checkCode == 200){
                            
                            // 正常
                            jump($db,$dwz_zzym,$key);
                        }else{
                            
                            // 不正常
                            // 发送通知
                            sendNotification($domainCheck_channel,'短网址'.$dwz_zzym.'域名被封了！尽快处理！',$db);
                            
                            // 是否有备用域名
                            if($domainCheck_byym){
                                
                                // 使用备用域名跳转
                                jump($db,$domainCheck_byym,$key);
                            }else{
                                
                                // 没有
                                // 获取失败
                                echo warnInfo('温馨提示','无法正常跳转或展示');
                            }
                        }
                    }else{
                        
                        // 关闭
                        jump($db,$dwz_zzym,$key);
                    }
                }
            }else{
                
                // 获取失败
                echo warnInfo('温馨提示','页面不存在或已被管理员删除');
            }
        }else{
            
            // 参数为空
            echo warnInfo('温馨提示','请求参数为空');
        }
        
        // 跳转
        function jump($db,$dwz_zzym,$key){
            
            // 获取当前dwzKey是否启用轮询域名
            $checkLunXunStatus = $db->set_table('huoma_dwz')->find(['dwz_key' => $key]);
            $dwz_lxymStatus = $checkLunXunStatus['dwz_lxymStatus'];
            if($dwz_lxymStatus && $dwz_lxymStatus == 1) {
                
                // 启用
                // 获取轮询域名列表
                $getLunXunDomains = $db->set_table('huoma_domain')->findAll(
                    ['domain_type' => 6],
                    $order='id desc',
                    $fields='domain',
                    $limit=null
                );
                
                // 从获取到的列表随机获取一个域名
                $randomKey = array_rand($getLunXunDomains);
                
                // 随机取出的轮询域名
                $lunxun_domain = $getLunXunDomains[$randomKey]['domain'];
                
                // 用轮询域名跳转到轮询页面
                header('HTTP/1.1 301 Moved Permanently');
                
                // 拼接跳转链接
                $jumpUrl = dirname($lunxun_domain . $_SERVER['REQUEST_URI']) . '/lx/?key=' . $key.'&t='.time();
                header('Location:'.$jumpUrl);
            }else {
                
                // 未启用
                // 使用中转域名跳转
                // 拼接落地页链接
                $jumpUrl = dirname(dirname($dwz_zzym.$_SERVER['REQUEST_URI'])).'/?key='.$key.'&t='.time();
                header('Location:'.$jumpUrl);
            }
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