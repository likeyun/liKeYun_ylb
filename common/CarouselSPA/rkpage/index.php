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
        <meta name="referrer" content="origin-when-cross-origin">
        <meta name="referrer" content="strict-origin-when-cross-origin">
        <script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/jquery/3.6.0/jquery.min.js"></script>
        <link rel="stylesheet" href="../../../static/css/common.css">
    </head>
    
    <body>
        
        <?php
        
            // 获取参数
            $Carousel_key = trim($_GET['ckey']);
            
            // 过滤
            if(preg_match('/[_\-\/\[\].,:;\'"=+*`~!@#$%^&()]/',$Carousel_key)){
               
                echo '该链接不安全，请重新生成！';
                exit;
            }
            if(preg_match('/(select|update|drop|DROP|insert|create|delete|where|join|script)/i',$Carousel_key)){
               
                echo '该链接不安全，请重新生成！';
                exit;
            }
            
            if($Carousel_key) {
                
                // 数据库
                include '../../../console/Db.php';
                $db = new DB_API($config);
                
                // 目录级别
                $folderNum = $config['folderNum'];
                
                // 查询当前 Carousel_key 是否存在
                $checkKey = $db->set_table('ylb_CarouselSPA')->find(['Carousel_key' => $Carousel_key]);
                
                if($checkKey) {
                    
                    // 获取当前链接的管理账号
                    $currentKeyUser = $checkKey['page_create_user'];
                    
                    // 获取用户信息
                    $getUserInfo = $db->set_table('huoma_user')->findAll(
                        $conditions = ['user_name' => $currentKeyUser],
                        $order = 'id asc',
                        $fields = 'user_status',
                        $limit = null
                    );
                    
                    // 检查创建者的状态
                    if($getUserInfo[0]['user_status'] == 2) {
                        
                        // 账号已被停止使用
                        echo warnInfo('提示','当前链接的管理账号已被停止使用');
                        exit;
                    }
                    
                    // 检查页面状态
                    if($checkKey['Carousel_status'] == 2) {
                        
                        // 停止使用
                        echo warnInfo('提示','当前链接已被管理员停止使用');
                        exit;
                    }
                    
                    // 落地域名
                    $Carousel_ldym = $checkKey['Carousel_ldym'];
                    
                    // Carousel_id
                    $Carousel_id = $checkKey['Carousel_id'];
                    
                    // 跳转到落地页
                    $jumpToldym = dirname(dirname($Carousel_ldym . $_SERVER['REQUEST_URI'])).'/?carousel_id='.$Carousel_id;
                    header('Location:' . $jumpToldym . '&exportKey='.MD5(time()).'&signature='.hash('sha256', $Carousel_id.time()));
                }else {
                    
                    // 不存在
                    echo warnInfo('提示','当前链接不存在或已被管理员删除');
                    exit;
                }
            }else {
                
                echo warnInfo('提示','参数不完整！');
                exit;
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