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
        <!--样式文件-->
        <link rel="stylesheet" href="../../static/css/common.css">
        <link rel="stylesheet" href="../../static/css/bootstrap.min.css">
        <!--公共JS-->
        <script src="../../static/js/jquery.min.js"></script>
    </head>
<body style="background:#fff;">
    
<?php

    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数（intval函数用于过滤特殊字符防止SQL注入）
    $sid = trim(intval($_GET['sid']));
    
    // 过滤参数
    if($sid && $sid !== ''){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取分享卡片信息
        $getshareCardInfo = ['shareCard_id'=>$sid];
        $getshareCardInfoResult = $db->set_table('huoma_shareCard')->find($getshareCardInfo);
        
        // 验证该中间页是否存在
        if($getshareCardInfoResult && $getshareCardInfoResult > 0){
            
            // 存在
            // 解析所需字段
            // 目标链接
            $shareCard_url = getSqlData($getshareCardInfoResult,'shareCard_url');
            
            // 状态
            $shareCard_status = getSqlData($getshareCardInfoResult,'shareCard_status');
            
            if($shareCard_status == 1){
                
                // 更新当前分享卡片的访问量
                updateThisshareCardPv($db,$sid);
            
                // 跳转
                header('Location:'.$shareCard_url);
            }else{
                
                echo '<title>温馨提示</title>';
                echo warnningInfo('该链接已被管理员暂停使用');
            }
            
        }else{
            
            // 不存在
            // 获取不到该shareCard_id的详情
            echo '<title>温馨提示</title>';
            echo warnningInfo('该链接不存在或已被管理员删除');
        } // if($getshareCardInfoResult && $getshareCardInfoResult > 0){
        
    } // if($sid && $sid !== '')
    
    /**
     * 以下是封装的一些操作函数
     * 一方面是便于多处调用
     * 另一方面是保持代码的整洁可读性
     */
    
    // 更新当前中间页的访问量
    function updateThisshareCardPv($db,$sid){
        
        // 传入sid
        $updateThisshareCardPv = 'UPDATE huoma_shareCard SET shareCard_pv=shareCard_pv+1 WHERE shareCard_id="'.$sid.'"';
        $db->set_table('huoma_shareCard')->findSql($updateThisshareCardPv);
    }
    
    // 解析数组
    function getSqlData($result,$field){
        
        // 传入数组和需要解析的字段
        return json_decode(json_encode($result))->$field;
    }
    
    // 提醒文字
    function warnningInfo($warnningText){
        
        // 传入warnningText
        return '<div id="warnning"><img src="../../static/img/warnning.svg" /></div><p id="warnningText">'.$warnningText.'</p>';
        
    }
    
?>

</body>
</html>