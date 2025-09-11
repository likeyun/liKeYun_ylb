<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
    $static_time = time();
?>
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
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
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
    
        // 参数
        $cid = intval($_GET['cid'] ?? 0);
    
        if($cid){
            // 数据库配置
            include '../../../console/Db.php';
            $db_host = $config['db_host'];
            $db_name = $config['db_name'];
            $db_user = $config['db_user'];
            $db_pass = $config['db_pass'];
    
            try {
                $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("数据库连接失败: " . $e->getMessage());
            }
    
            // 查询函数
            function pdo_find($pdo, $table, $where){
                $field = key($where);
                $value = $where[$field];
                $sql = "SELECT * FROM `$table` WHERE `$field` = :value LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':value'=>$value]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
    
            // 根据cid获取落地域名
            $getChannelInfo = pdo_find($pdo,'huoma_channel',['channel_id'=>$cid]);
            if($getChannelInfo){
                $channel_ldym = $getChannelInfo['channel_ldym'];
                
                // 跳转
                jump($channel_ldym,$cid);
            }else{
                echo warnInfo('温馨提示','页面不存在或已被管理员删除');
            }
        }else{
            echo warnInfo('温馨提示','请求参数为空');
        }
    
        // 跳转
        function jump($channel_ldym,$cid){
            
            // 获取落地页的URL
            $longUrl = dirname(dirname($channel_ldym.$_SERVER['REQUEST_URI'])).'/?cid='.$cid;
            
            // JS跳转
            echo '<script>location.href="'.$longUrl.'";</script>';
        }
    
        // 提示信息
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