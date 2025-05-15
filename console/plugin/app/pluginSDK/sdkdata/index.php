<html>
    <head>
        <meta name="wechat-enable-text-zoom-em" content="true">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <link rel="shortcut icon" href="https://res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico">
        <link rel="stylesheet" href="../../static/css/common.css">
        <link rel="stylesheet" href="data.css">
        <script type="text/javascript" src="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-1-M/jquery/3.1.1/jquery.min.js"></script>
    </head>
    <body>
        
    <?php
    
        // 页面编码
        header("Content-type:text/html;charset=utf-8");
        
        // 获取参数
        $data_key = trim($_GET['key']);
        
        // 过滤不安全的字符
        if(preg_match('/[_\-\/\[\].,:;\'"=+*`~!@#$%^&()]/',$data_key)){
           
            echo warnInfo('温馨提示','该链接不安全，请重新生成！');
            exit;
        }
        if(preg_match('/(select|update|drop|DROP|insert|create|delete|where|join|script)/i',$data_key)){
           
            echo warnInfo('温馨提示','该链接不安全，请重新生成！');
            exit;
        }
        
        // 过滤参数
        if($data_key){
            
            // 数据库配置
            include '../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);
            
            // 获取当前 data_key 的详情
            $getPageInfo = $db->set_table('ylbPlugin_sdk')->find(['data_key' => $data_key]);
            
            // 1. 检查当前链接的管理账号有效期
            // 2. 检测当前链接的管理账号状态
            
            // 获取当前链接的管理账号
            $currentKeyUser = $getPageInfo['data_create_user'];
            
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
                echo warnInfo('提示','当前页面的管理账号已被停止使用');
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

            if($getPageInfo){
                
                // 检查当前页面是否已达到过期时间
                $data_expire_time = $getPageInfo['data_expire_time'];
                $current_time = date("Y-m-d H:i:s"); // 当前时间
                if (strtotime($current_time) >= strtotime($data_expire_time)) {
                    
                    // 页面已到期
                    echo warnInfo('提示','当前页面已过期');
                    exit;
                }
                
                // 状态
                $data_status = $getPageInfo['data_status'];
                
                // 判断状态
                if($data_status == 1) {
                    
                    // 正常
                    // 访问限制检测（仅限手机打开）
                    if($getPageInfo['data_limit'] == 2 && !preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone|webOS|BlackBerry/i', $_SERVER['HTTP_USER_AGENT'])) {
                        
                        echo warnInfo('提示','请在手机设备打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限微信内打开）
                    if($getPageInfo['data_limit'] == 3 && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === FALSE) {
                        
                        echo warnInfo('提示','请在微信内打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限QQ内打开）
                    if($getPageInfo['data_limit'] == 4 && strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/') === FALSE && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === FALSE) {
                        
                        echo warnInfo('提示','请在QQ内打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限抖音内打开）
                    if($getPageInfo['data_limit'] == 5 && (strpos($_SERVER['HTTP_USER_AGENT'], 'aweme') === FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Bytedance') === FALSE)) {
                        
                        echo warnInfo('提示','请在抖音内打开页面');
                        exit;
                    }
                    
                    // 加载数据
                    // 所有校验已通过
                    // 这里就可以开始编写你的业务逻辑了
                    echo '<div class="data-info">
                        <title>'.$getPageInfo['data_title'].'</title>
                        <img src="'.$getPageInfo['data_pic'].'" />
                        <a href="'.$getPageInfo['data_jumplink'].'" class="jump-btn">点击跳转</a>
                    </div>';
                }else {
                    
                    // 停用
                    echo warnInfo('提示','该页面已被管理员停止使用');
                }
            }else{
                
                // 不存在
                echo warnInfo('提示','页面不存在或已被管理员删除');
            }
        }else {
            
            // 参数不完整
            echo warnInfo('提示','参数不完整！');
        }
        
        // 提醒文字
        function warnInfo($title,$warnText){
            
            return '
            <title>'.$title.'</title>
            <div id="warnning">
                <img src="../../static/img/warn.png" />
            </div>
            <p id="warnText">'.$warnText.'</p>';
        }
    ?>
    </body>
    <script>
        
        // 从URL中获取参数
        const urlParams = new URLSearchParams(window.location.search);
        const key = urlParams.get('key'); // 获取 key 参数
        const exportKey = urlParams.get('exportKey'); // 获取 exportKey 参数
        if (key && exportKey) {
            
            // 请求
            getData(key, exportKey);
        }else {
            
            // 参数缺失
            document.title = '提醒';
            $('#chat-box').html('<div class="warnInfo"><div class="warn_icon"></div>参数缺失，无法加载页面！</div>');
        }
    </script>
</html>