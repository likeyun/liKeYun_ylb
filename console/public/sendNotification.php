<?php

    // 企业微信
    if($noti_type == "企业微信"){
        
        // 获取企业微信推送配置
        $notificationConfig = $db->set_table('huoma_notification')->find(['id'=>1]);
        if($notificationConfig){
            
            // 获取当前HTTP协议
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            
            // 获取当前域名
            $domainName = $_SERVER['HTTP_HOST'];
            
            // 获取当前文件所在目录
            $directory = dirname($_SERVER['PHP_SELF']);
            
            // strstr()函数提取common前面的目录
            $common_beforeUrl = $protocol . $domainName . strstr($directory, 'common', true);
            
            // 构建完整的Url
            $postUrl = $common_beforeUrl . 'console/public/qywx.php?noti_text='.$noti_text;
            
            // 请求
            file_get_contents($postUrl);
        }
    }
    
    // 电子邮件
    if($noti_type == "邮件"){
        
        // 获取电子邮件推送配置
        $notificationConfig = $db->set_table('huoma_notification')->find(['id'=>1]);
        if($notificationConfig){
            
            // 安全码
            // 这个安全码需要跟/console/public/emailSend/index.php里面的第14行代码一致
            // 如需修改，请前往上述路径修改
            $aqm = 123456;
            
            // 获取当前HTTP协议
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            
            // 获取当前域名
            $domainName = $_SERVER['HTTP_HOST'];
            
            // 获取当前文件所在目录
            $directory = dirname($_SERVER['PHP_SELF']);
            
            // strstr()函数提取common前面的目录
            $common_beforeUrl = $protocol . $domainName . strstr($directory, 'common', true);
            
            // 构建完整的Url
            $postUrl = $common_beforeUrl . 'console/public/emailSend/?noti_text='.$noti_text.'&aqm='.$aqm;
            
            // 请求
            file_get_contents($postUrl);
        }
    }
    
    // Bark
    if($noti_type == "Bark"){
        
        // 获取Bark推送配置
        $notificationConfig = $db->set_table('huoma_notification')->find(['id'=>1]);
        if($notificationConfig){
            
            // 有配置
            $barkConfig = getSqlData($notificationConfig,'bark_url');
            
            // 执行推送
            file_get_contents($barkConfig.urlencode($noti_text));
        }
    }
    
    // Server酱
    if($noti_type == "Server酱"){
        
        // 获取Server酱推送配置
        $notificationConfig = $db->set_table('huoma_notification')->find(['id'=>1]);
        if($notificationConfig){
            
            // 有配置
            $SendKeyConfig = getSqlData($notificationConfig,'SendKey');
            
            // 拼接推送链接
            $ServerJiangURL = "https://sctapi.ftqq.com/".$SendKeyConfig.".send?title=".urlencode($noti_text);
            
            // 执行推送
            file_get_contents($ServerJiangURL);
        }
    }
    
    // HTTP
    if($noti_type == "HTTP"){
        
        // 获HTTP推送配置
        $notificationConfig = $db->set_table('huoma_notification')->find(['id'=>1]);
        if($notificationConfig){
            
            // 有配置
            $HTTP_URL = getSqlData($notificationConfig,'http_url');
            
            // 发送POST
            $data = $noti_text;
            $ch = curl_init($HTTP_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $response = curl_exec($ch);
            curl_close($ch);
        }
    }

?>