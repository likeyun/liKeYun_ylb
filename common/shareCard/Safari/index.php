<link rel="stylesheet" href="../../../static/css/common.css">
<?php

    // 获取ID
    $shareCard_id = trim(intval($_GET['sid']));
    
    if($shareCard_id) {
        
        // 数据库配置
        include '../../../console/Db.php';
        $db = new DB_API($config);
    
        // 获取详情
        $getCardInfo = $db->set_table('huoma_shareCard')->find(['shareCard_id' => $shareCard_id]);
        
        // 标题
        $shareCard_title = $getCardInfo['shareCard_title'];
        
        // 描述
        $shareCard_desc = $getCardInfo['shareCard_desc'];
        
        // 图标
        $shareCard_img = $getCardInfo['shareCard_img'];
        
        // 目标链接
        $shareCard_url = $getCardInfo['shareCard_url'];
        
        // 状态
        $shareCard_status = $getCardInfo['shareCard_status'];
        
        if($shareCard_status == 2) {
            
            // 停用
            echo warnInfo('温馨提示','该链接已被管理员暂停使用!');
            exit;
        }
        
    }else {
        
        echo warnInfo('温馨提示','参数缺失!');
        exit;
    }
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="renderer" content="webkit">
        <meta name="layoutmode" content="standard">
        <meta name="imagemode" content="force">
        <meta name="wap-font-scale" content="no">
        <meta name="format-detection" content="telephone=no">
        <title>Safari生成分享卡片</title>
        <meta property="og:type" content="website">
        <meta property="og:title" content="<?php echo $shareCard_title; ?>">
        <meta property="og:description" content="<?php echo $shareCard_desc; ?>">
        <meta property="og:image" content="<?php echo $shareCard_img; ?>">
        <link rel="shortcut icon" href="../../../static/img/safari-fav.png">
    </head>
    
    <body>
        
        <?php
            
            if(getBrowserType() == 'WeChat') {
                
                // 微信
                // 跳转到目标链接
                echo '<title>正在跳转...</title>';
                header('Location:' . $shareCard_url);
            }else if(getBrowserType() == 'Safari') {
                
                // Safari
                echo '<img src="../../../static/img/share_yindao.jpg" style="width:100%;" />';
            }else {
                
                // 其他
                echo warnInfo('温馨提示','当前浏览器不支持!');
            }
            
            // 获取打开当前页面的浏览器
            function getBrowserType() {
                
                // 获取HTTP_USER_AGENT
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
            
                // 检测是否在微信内
                if (strpos($userAgent, 'MicroMessenger') !== false) {
                    return 'WeChat';
                }
            
                // 检测是否是Safari
                if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
                    return 'Safari';
                }
            
                return 'Unknown';
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