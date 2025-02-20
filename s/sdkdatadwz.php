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
            $data_key = trim($_GET['key']);
            
            // 过滤
            if(preg_match('/[_\-\/\[\].,:;\'"=+*`~!@#$%^&()]/',$data_key)){
               
                echo '该链接不安全，请重新生成！';
                exit;
            }
            if(preg_match('/(select|update|drop|DROP|insert|create|delete|where|join|script)/i',$data_key)){
               
                echo '该链接不安全，请重新生成！';
                exit;
            }
            
            if($data_key) {
                
                // 数据库
                include '../console/Db.php';
                $db = new DB_API($config);
                
                // 目录级别
                $folderNum = $config['folderNum'];
                
                // 查询当前 data_key 是否存在
                $checkKey = $db->set_table('ylbPlugin_sdk')->find(['data_key' => $data_key]);
                
                if($checkKey) {
                    
                    // Key存在
                    // 1. 检查当前链接的管理账号有效期
                    // 2. 检测当前链接的管理账号状态
                    
                    // 获取当前链接的管理账号
                    $currentKeyUser = $checkKey['data_create_user'];
                    
                    // 获取用户信息
                    $getUserInfo = $db->set_table('huoma_user')->findAll(
                        $conditions = ['user_name' => $currentKeyUser],
                        $order = 'id asc',
                        $fields = 'user_status',
                        $limit = null
                    );
                    
                    // user_expire_time
                    
                    // 检查创建者的状态
                    if($getUserInfo[0]['user_status'] == 2) {
                        
                        // 账号已被停止使用
                        echo warnInfo('提示','当前链接的管理账号已被停止使用');
                        exit;
                    }
                    
                    // // 当前链接的管理者的账号有效期
                    // $current_user_expire_time = strtotime($getUserInfo[0]['user_expire_time']);
                    
                    // // 对比时间
                    // if(time() > $current_user_expire_time) {
                        
                    //     // 账号已过期
                    //     echo warnInfo('提示','当前链接的管理账号已到期');
                    //     exit;
                    // }
                    
                    // 检查页面状态
                    if($checkKey['data_status'] == 2) {
                        
                        // 停止使用
                        echo warnInfo('提示','当前链接已被管理员停止使用');
                        exit;
                    }
                    
                    // 检查当前页面是否已达到过期时间
                    $data_expire_time = $checkKey['data_expire_time'];
                    $current_time = date("Y-m-d H:i:s"); // 当前时间
                    if (strtotime($current_time) >= strtotime($data_expire_time)) {
                        
                        // 页面已到期
                        echo warnInfo('提示','当前页面已过期');
                        exit;
                    }
                    
                    // 访问限制检测（仅限手机打开）
                    if($checkKey['data_limit'] == 2 && !preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone|webOS|BlackBerry/i', $_SERVER['HTTP_USER_AGENT'])) {
                        
                        echo warnInfo('提示','请在手机设备打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限微信内打开）
                    if($checkKey['data_limit'] == 3 && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === FALSE) {
                        
                        echo warnInfo('提示','请在微信内打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限QQ内打开）
                    if($checkKey['data_limit'] == 4 && strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/') === FALSE && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === FALSE) {
                        
                        echo warnInfo('提示','请在QQ内打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限抖音内打开）
                    if($checkKey['data_limit'] == 5 && (strpos($_SERVER['HTTP_USER_AGENT'], 'aweme') === FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Bytedance') === FALSE)) {
                        
                        echo warnInfo('提示','请在抖音内打开页面');
                        exit;
                    }
                    
                    // 入口域名
                    $data_rkym = $checkKey['data_rkym'];
                    
                    // 跳转到入口页面
                    jumpTo($folderNum,$data_rkym,'sdkdata','dkey',$data_key);
                }else {
                    
                    // 不存在
                    echo warnInfo('提示','当前链接不存在或已被管理员删除');
                    exit;
                }
            }else {
                
                echo warnInfo('提示','参数不完整！');
                exit;
            }
            
            // 跳转到
            function jumpTo($folderNum,$rkym,$commonFolder,$paramName,$paramVal){
                
                if($folderNum == 1){
                        
                    // 根目录
                    $longUrl = $rkym.'/common/'.$commonFolder.'/rkpage/?'.$paramName.'='.$paramVal.'&exportKey='.MD5(time()).'&signature='.hash('sha256', $paramVal.time());
                }else{
                    
                    // 其他目录
                    $longUrl = $rkym.'/'.redirectURL($folderNum).'/common/'.$commonFolder.'/rkpage/?'.$paramName.'='.$paramVal.'&exportKey='.MD5(time()).'&signature='.hash('sha256', $paramVal.time());
                }
                
                // 跳转
                header('Location:'.$longUrl);
            }
            
            // 目录级别
            function redirectURL($folderNum){
                
                if($folderNum == 2){
                    
                    // 二级目录
                    return basename(dirname(dirname(__FILE__)));
                }else if($folderNum == 3){
                    
                    // 三级目录
                    return basename(dirname(dirname(dirname(__FILE__)))).'/'.basename(dirname(dirname(__FILE__)));
                }else if($folderNum == 4){
                    
                    // 四级目录
                    $oneFolder = basename(dirname(dirname(dirname(dirname(__FILE__))))).'/';
                    $twoFolder = basename(dirname(dirname(dirname(__FILE__)))).'/';
                    $threeFolder = basename(dirname(dirname(__FILE__)));
                    return $oneFolder.$twoFolder.$threeFolder;
                }
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