<?php

	// 编码
	header("Content-type:application/json");
	ini_set("display_errors", "Off");
	
    // 获取参数
    $db_host = trim($_POST['db_host']);
    $db_user = trim($_POST['db_user']);
    $db_pass = trim($_POST['db_pass']);
    $db_name = trim($_POST['db_name']);
    $user_email = trim($_POST['user_email']);
    $user_name = trim($_POST['user_name']);
    $user_pass = trim($_POST['user_pass']);
    $folder = trim($_POST['install_folder']);
    
    // 禁止一些字符
    if(
        preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_name) || 
        preg_match("/[\',:;*?~`!#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_email) || 
        preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
            
            $result = array(
		        'code' => 203,
                'msg' => '你输入的管理员邮箱、账号、密码可能包含了一些不安全字符，请更换~'
	        );
	        echo json_encode($result,JSON_UNESCAPED_UNICODE);
	        exit;
    }else if(
        preg_match("/(and|or|select|update|drop|DROP|insert|create|delete|like|where|join|script|set)/i",$user_name) || 
        preg_match("/(and|or|select|update|drop|DROP|insert|create|delete|like|where|join|script|set)/i",$user_pass)
    ){
        
        $result = array(
	        'code' => 203,
            'msg' => '你输入的管理员账号、密码包含了一些不安全字符'
        );
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 过滤参数
    if(empty($db_host) || !isset($db_host)){
        
        $result = array(
            'code' => 203,
            'msg' => '数据库地址未填写（无需带3306端口号）'
        );
    }else if(empty($db_host) || !isset($db_user)){
        
        $result = array(
            'code' => 203,
            'msg' => '数据库账号未填写'
        );
    }else if(empty($db_pass) || !isset($db_pass)){
        
        $result = array(
            'code' => 203,
            'msg' => '数据库密码未填写'
        );
    }else if(empty($db_name) || !isset($db_name)){
        
        $result = array(
            'code' => 203,
            'msg' => '数据库名称未填写'
        );
    }else if(empty($user_email) || !isset($user_email)){
        
        $result = array(
            'code' => 203,
            'msg' => '管理员邮箱未填写'
        );
    }else if(empty($user_name) || !isset($user_name)){
        
        $result = array(
            'code' => 203,
            'msg' => '管理员账号未填写'
        );
    }else if(strlen($user_name) < 5){
        
        $result = array(
            'code' => 203,
            'msg' => '账号不得小于5位数'
        );
    }else if(strlen($user_name) > 15){
        
        $result = array(
            'code' => 203,
            'msg' => '账号不得大于15位数'
        );
    }else if(preg_match("/[\x7f-\xff]/", $user_name)){
    
        $result = array(
		    'code' => 203,
            'msg' => '账号不能存在中文'
	    );
    }else if(preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_name)){
    
        $result = array(
		    'code' => 203,
            'msg' => '账号不能存在特殊字符'
	    );
    }else if(empty($user_pass) || !isset($user_pass)){
        
        $result = array(
            'code' => 203,
            'msg' => '管理员密码未填写'
        );
    }else if(strlen($user_pass) < 5){
            
        $result = array(
            'code' => 203,
            'msg' => '密码不得小于5位数'
        );
    }else if(strlen($user_pass) > 32){
        
        $result = array(
            'code' => 203,
            'msg' => '密码不得大于32位数'
        );
    }else if(preg_match("/[\x7f-\xff]/", $user_pass)){
    
        $result = array(
		    'code' => 203,
            'msg' => '密码不能存在中文'
	    );
    }else if(preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
    
        $result = array(
		    'code' => 203,
            'msg' => '密码不能存在特殊字符'
	    );
    }else if(empty($folder) || !isset($folder)){
        
        $result = array(
            'code' => 203,
            'msg' => '安装目录级别未选择'
        );
    }else{
        
        // 开始连接数据库
        $conn = new mysqli();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
        $conn->real_connect($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_errno) {
            
            // 错误信息
            $errorMsg = $conn->connect_error;
        
            if (stripos($errorMsg, 'timed out') !== false) {
                $result = [
                    'code' => 202,
                    'msg' => '连接超时，可能是数据库服务器地址有误导致连接失败！建议检查数据库服务器IP地址是否开放远程连接权限，如果服务器和数据库是同一个服务器，则直接填写 localhost 即可。'
                ];
            } else if (preg_match('/getaddrinfo failed/i', $errorMsg)) {
                $result = [
                    'code' => 202,
                    'msg' => '数据库地址有误'
                ];
            } else if (preg_match('/using password/i', $errorMsg)) {
                $result = [
                    'code' => 202,
                    'msg' => '数据库账号或密码有误'
                ];
            } else if (preg_match('/database /i', $errorMsg)) {
                $result = [
                    'code' => 202,
                    'msg' => '数据库名错误，你的数据库服务器不存在这个数据库名。'
                ];
            } else {
                $result = [
                    'code' => 202,
                    'msg' => '数据库连接失败：' . $errorMsg
                ];
            }
        }else{
            
            // 数据库连接成功
            
            if(preg_match("/already exists/", $conn->error)){
                $result = array(
                    'code' => 202,
                    'msg' => '请勿重复安装！如需重新安装请前往数据库删除 huoma_前缀、ylb_前缀 的表！'
                );
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 用户表
            $huoma_user = "CREATE TABLE `huoma_user` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `user_id` int(10) DEFAULT NULL COMMENT '用户ID',
              `user_name` varchar(32) DEFAULT NULL COMMENT '账号',
              `user_pass` varchar(64) DEFAULT NULL COMMENT '密码',
              `user_email` text COMMENT '邮箱',
              `user_mb_ask` text COMMENT '密保问题',
              `user_mb_answer` varchar(32) DEFAULT NULL COMMENT '密保答案',
              `user_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
              `user_expire` varchar(32) DEFAULT NULL COMMENT '到期时间',
              `user_admin` int(2) DEFAULT '2' COMMENT '管理权限（1是 2否）',
              `user_manager` varchar(32) DEFAULT NULL COMMENT '账号管理者',
              `user_beizhu` varchar(64) DEFAULT NULL COMMENT '备注信息',
              `user_group` varchar(32) DEFAULT NULL COMMENT '用户组',
              `user_status` int(2) NOT NULL DEFAULT '1' COMMENT '账号状态（1可用 2停用）'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            // 设置管理员账号和密码
            $now = new DateTime();
            $now->modify('+10 years');
            $ten_year_later = $now->format('Y');
            $huoma_user_Data = "INSERT INTO `huoma_user` (`user_id`, `user_name`, `user_pass`, `user_expire`, `user_email`, `user_mb_ask`, `user_admin`, `user_manager`, `user_beizhu`, `user_group`) VALUES (100000, '".$user_name."', '".MD5($user_pass)."',  '$ten_year_later-12-31 23:59:59', '".$user_email."','请选择密保问题', 1, '".$user_name."', '超级管理员', '超管')";
            
            // 群活码
            $huoma_qun = "CREATE TABLE `huoma_qun` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `qun_id` int(10) DEFAULT NULL COMMENT '群ID',
              `qun_title` varchar(64) DEFAULT NULL COMMENT '群标题',
              `qun_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开启 2关闭）',
              `qun_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `qun_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `qun_today_pv` varchar(64) DEFAULT NULL COMMENT '今天访问量',
              `qun_qc` int(2) NOT NULL DEFAULT '2' COMMENT '去重（1开启 2关闭）',
              `qun_notify` varchar(32) DEFAULT NULL COMMENT '通知渠道',
              `qun_rkym` text COMMENT '入口域名',
              `qun_ldym` text COMMENT '落地域名',
              `qun_dlym` text COMMENT '短链域名',
              `qun_kf` text COMMENT '客服二维码',
              `qun_kf_status` int(2) NOT NULL DEFAULT '2' COMMENT '客服开启状态（1开启 2关闭）',
              `qun_safety` int(2) NOT NULL DEFAULT '1' COMMENT '顶部安全提示（1显 2隐）',
              `qun_beizhu` text COMMENT '群备注',
              `qun_key` varchar(10) DEFAULT NULL COMMENT '短链接Key',
              `qun_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群活码列表'";
            
            // 群活码下的群二维码
            $huoma_qun_zima = "CREATE TABLE `huoma_qun_zima` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `qun_id` int(10) DEFAULT NULL COMMENT '群活码ID',
              `zm_id` int(10) DEFAULT NULL COMMENT '群子码ID',
              `zm_yz` int(10) NOT NULL DEFAULT '200' COMMENT '阈值',
              `zm_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `longpress_num` int(10) NOT NULL DEFAULT '0' COMMENT '长按次数',
              `zm_qrcode` text COMMENT '二维码URL',
              `zm_leader` varchar(32) DEFAULT NULL COMMENT '群主微信号',
              `zm_update_time` varchar(32) DEFAULT NULL COMMENT '更新时间',
              `zm_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开 2关）'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群活码下的群二维码'";
            
            // 并流
            $ylb_qun_bingliu = "CREATE TABLE `ylb_qun_bingliu` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `bingliu_id` int(10) DEFAULT NULL COMMENT '并流ID',
              `before_qun_id` int(10) DEFAULT NULL COMMENT '原活码ID',
              `before_qun_key` varchar(10) DEFAULT NULL COMMENT '原活码Key',
              `later_qun_id` int(10) DEFAULT NULL COMMENT '并入活码ID',
              `bingliu_num` int(10) NOT NULL DEFAULT '0' COMMENT '并流次数',
              `bingliu_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开 2关）',
              `createUser` varchar(32) DEFAULT NULL COMMENT '操作用户'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群活码下的群二维码'";
            
            // 客服码
            $huoma_kf = "CREATE TABLE `huoma_kf` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kf_id` int(10) DEFAULT NULL COMMENT '客服码ID',
              `kf_title` varchar(64) DEFAULT NULL COMMENT '客服标题或昵称',
              `kf_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `kf_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `kf_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `kf_qc` int(1) NOT NULL DEFAULT '2' COMMENT '去重1开 2关',
              `kf_today_pv` varchar(64) DEFAULT NULL COMMENT '今天的访问量',
              `kf_rkym` text COMMENT '入口域名',
              `kf_ldym` text COMMENT '落地域名',
              `kf_dlym` text COMMENT '短链域名',
              `kf_model` int(2) DEFAULT NULL COMMENT '展示模式（1阈值 2随机）',
              `kf_online` int(2) NOT NULL DEFAULT '2' COMMENT '在线状态（1显 2隐）',
              `kf_onlinetimes` text COMMENT '在线时间JSON配置',
              `kf_key` varchar(10) DEFAULT NULL COMMENT '短链接Key',
              `kf_safety` int(2) NOT NULL DEFAULT '1' COMMENT '顶部安全提示（1显 2隐）',
              `kf_beizhu` text COMMENT '备注',
              `kf_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客服码'";
            
            // 客服码下的微信二维码
            $huoma_kf_zima = "CREATE TABLE `huoma_kf_zima` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kf_id` int(10) DEFAULT NULL COMMENT '客服码ID',
              `zm_id` int(10) DEFAULT NULL COMMENT '子码ID',
              `zm_yz` int(10) DEFAULT '0' COMMENT '阈值',
              `zm_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `longpress_num` int(10) NOT NULL DEFAULT '0' COMMENT '长按次数',
              `zm_qrcode` text COMMENT '二维码URL',
              `zm_num` varchar(32) DEFAULT NULL COMMENT '客服微信号',
              `zm_update_time` varchar(32) DEFAULT NULL COMMENT '更新时间',
              `zm_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开 2关）	'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客服码下的微信二维码'";
            
            // 域名
            $huoma_domain = "CREATE TABLE `huoma_domain` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `domain_id` int(10) DEFAULT NULL COMMENT '域名ID',
              `domain_type` int(2) DEFAULT NULL COMMENT '域名类型（1入口 2落地 3短链 4备用 5对象存储）',
              `domain` text COMMENT '域名',
              `domain_beizhu` varchar(32) DEFAULT NULL COMMENT '备注',
              `domain_usergroup` varchar(255) DEFAULT NULL COMMENT '授权用户组'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='域名/落地页'";
            
            // 获取http协议类型
            $HTTP_TYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            
            $domain = $HTTP_TYPE . $_SERVER['HTTP_HOST'];
            $domain = addslashes($domain);
            $huoma_domain_Data = "INSERT INTO `huoma_domain` (`domain_id`, `domain_type`, `domain_beizhu`, `domain`, `domain_usergroup`) VALUES
            (100000, 1, '入口专用', '{$domain}', '[\"超管\"]'),
            (100001, 2, '落地专用', '{$domain}', '[\"超管\"]'),
            (100002, 3, '短网址专用', '{$domain}', '[\"超管\"]')";
            
            // 渠道码
            $huoma_channel = "CREATE TABLE `huoma_channel` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `channel_id` int(10) DEFAULT NULL COMMENT '渠道ID',
              `channel_title` varchar(64) DEFAULT NULL COMMENT '渠道标题',
              `channel_status` int(2) DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `channel_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `channel_pv` int(10) DEFAULT '0' COMMENT '访问量',
              `Android_Total` int(10) DEFAULT '0' COMMENT 'Android设备数据量',
              `iOS_Total` int(10) DEFAULT '0' COMMENT 'iOS设备数据量',
              `Windows_Total` int(10) DEFAULT '0' COMMENT 'Windows设备数据量',
              `Linux_Total` int(10) DEFAULT '0' COMMENT 'Linux设备数据量',
              `MacOS_Total` int(10) DEFAULT '0' COMMENT 'MacOS设备数据量',
              `channel_DataTotal` int(10) DEFAULT '0' COMMENT '数据量',
              `channel_today_pv` varchar(64) DEFAULT NULL COMMENT '今天的访问量',
              `channel_rkym` text COMMENT '入口域名',
              `channel_ldym` text COMMENT '落地域名',
              `channel_dlym` text COMMENT '短链域名',
              `channel_key` varchar(10) DEFAULT NULL COMMENT '短链接key',
              `channel_url` text COMMENT '渠道目标链接',
              `channel_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='渠道码'";
            
            // 渠道码数据
            $huoma_channel_data = "CREATE TABLE `huoma_channel_data` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `data_id` int(10) DEFAULT NULL COMMENT '数据ID',
              `channel_id` int(10) DEFAULT NULL COMMENT '渠道ID',
              `data_referer` text COMMENT '数据来源',
              `data_device` varchar(32) DEFAULT NULL COMMENT '来源设备',
              `data_ip` varchar(32) DEFAULT NULL COMMENT '数据来源IP',
              `data_pv` int(10) DEFAULT '0' COMMENT '访问量',
              `data_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '数据来源的时间'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='渠道码数据'";
            
            // 渠道码黑名单IP
            $huoma_channel_accessdenied = "CREATE TABLE `huoma_channel_accessdenied` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `data_ip` varchar(32) DEFAULT NULL COMMENT 'IP',
              `accessdenied_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入时间',
              `add_user` varchar(32) DEFAULT NULL COMMENT '操作者账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='渠道码黑名单IP'";
            
            // 短网址
            $huoma_dwz = "CREATE TABLE `huoma_dwz` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `dwz_id` int(10) DEFAULT NULL COMMENT '短网址ID',
              `dwz_title` varchar(32) DEFAULT NULL COMMENT '标题',
              `dwz_key` varchar(10) DEFAULT NULL COMMENT '短网址Key',
              `dwz_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `dwz_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `dwz_today_pv` varchar(64) DEFAULT NULL COMMENT '今天访问量',
              `dwz_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `dwz_url` text DEFAULT NULL COMMENT '目标链接',
              `dwz_android_url` text DEFAULT NULL COMMENT 'Android设备目标链接',
              `dwz_ios_url` text DEFAULT NULL COMMENT 'iOS设备目标链接',
              `dwz_windows_url` text DEFAULT NULL COMMENT 'Windows设备目标链接',
              `dwz_type` int(2) DEFAULT NULL COMMENT '访问限制',
              `dwz_rkym` text COMMENT '入口域名',
              `dwz_zzym` text COMMENT '中转域名',
              `dwz_dlym` text COMMENT '短链域名',
              `dwz_lxymStatus` int(1) NOT NULL DEFAULT '2' COMMENT '轮询域名启用状态',
              `dwz_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短网址'";
            
            // 短网址API
            $huoma_dwz_apikey = "CREATE TABLE `huoma_dwz_apikey` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `apikey_user` varchar(32) DEFAULT NULL COMMENT '用户名',
              `apikey_id` int(10) DEFAULT NULL COMMENT 'ID',
              `apikey_ip` varchar(32) DEFAULT NULL COMMENT '白名单IP',
              `apikey` varchar(32) DEFAULT NULL COMMENT '开放接口ApiKey',
              `apikey_secrete` varchar(64) DEFAULT NULL COMMENT '开放接口密钥',
              `apikey_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `apikey_expire` varchar(32) DEFAULT NULL COMMENT '到期时间',
              `apikey_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `apikey_quota` int(20) DEFAULT '100000' COMMENT '请求配额（最大次数）',
              `apikey_num` int(20) NOT NULL DEFAULT '0' COMMENT '请求次数',
              `apikey_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短网址API'";
            
            // 淘宝客中间页
            $huoma_tbk = "CREATE TABLE `huoma_tbk` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `zjy_id` varchar(10) DEFAULT NULL COMMENT '中间页ID',
              `zjy_short_title` varchar(32) DEFAULT NULL COMMENT '短标题',
              `zjy_long_title` text COMMENT '长标题',
              `zjy_tkl` varchar(64) DEFAULT NULL COMMENT '淘口令',
              `zjy_rkym` text COMMENT '入口域名',
              `zjy_ldym` text COMMENT '落地域名',
              `zjy_dlym` text COMMENT '短链域名',
              `zjy_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `zjy_pv` varchar(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `zjy_copyNum` int(10) DEFAULT '0' COMMENT '复制次数',
              `zjy_original_cost` varchar(10) DEFAULT NULL COMMENT '原价',
              `zjy_discounted_price` varchar(10) DEFAULT NULL COMMENT '券后价',
              `zjy_goods_img` text COMMENT '商品主图',
              `zjy_goods_link` text COMMENT '商品链接',
              `zjy_key` varchar(10) DEFAULT NULL COMMENT '短链接',
              `zjy_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='淘宝客中间页'";
            
            // 淘宝客中间页配置
            $huoma_tbk_config = "CREATE TABLE `huoma_tbk_config` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `zjy_config_appkey` varchar(64) DEFAULT NULL COMMENT '折淘客appkey',
              `zjy_config_sid` varchar(32) DEFAULT NULL COMMENT '折淘客sid',
              `zjy_config_pid` varchar(64) DEFAULT NULL COMMENT '你的pid',
              `zjy_config_tbname` varchar(32) DEFAULT NULL COMMENT '淘宝账号',
              `zjy_config_user` varchar(32) DEFAULT NULL COMMENT '你的引流宝账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='淘宝客中间页配置'";
            
            // 分享卡片
            $huoma_shareCard = "CREATE TABLE `huoma_shareCard` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `shareCard_id` int(10) DEFAULT NULL COMMENT '卡片ID',
              `shareCard_title` varchar(64) DEFAULT NULL COMMENT '标题',
              `shareCard_desc` text COMMENT '摘要',
              `shareCard_img` text COMMENT '分享缩略图',
              `shareCard_ldym` text COMMENT '落地域名',
              `shareCard_url` text COMMENT '目标链接',
              `shareCard_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `shareCard_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `shareCard_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `shareCard_model` int(1) DEFAULT NULL COMMENT '分享模式',
              `shareCard_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分享卡片'";
            
            // 分享卡片配置
            $huoma_shareCardConfig = "CREATE TABLE `huoma_shareCardConfig` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `appid` varchar(32) DEFAULT NULL COMMENT '公众号appid',
              `appsecret` varchar(64) DEFAULT NULL COMMENT '公众号appsecret',
              `access_token` text COMMENT 'access_token',
              `access_token_expire_time` varchar(32) DEFAULT NULL COMMENT 'access_token_expire_time',
              `jsapi_ticket` text COMMENT 'jsapi_ticket',
              `jsapi_ticket_expire_time` varchar(32) DEFAULT NULL COMMENT 'jsapi_ticket_expire_time'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分享卡片配置'";
            
            // 域名检测
            $huoma_domainCheck = "CREATE TABLE `huoma_domainCheck` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `domainCheck_status` int(1) NOT NULL DEFAULT '2' COMMENT '状态',
              `domainCheck_channel` varchar(32) DEFAULT NULL COMMENT '通知渠道',
              `domainCheck_byym` text COMMENT '备用域名'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='域名检测'";
            
            // 添加默认域名检测配置
            $huoma_domainCheck_Data = "INSERT INTO `huoma_domainCheck` (`domainCheck_status`,`domainCheck_channel`,`domainCheck_byym`) VALUES (2,'企业微信','".$HTTP_TYPE.$_SERVER['HTTP_HOST']."')";
            
            // 24小时访问量统计
            $huoma_hourNum = "CREATE TABLE `huoma_hourNum` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `hourNum_type` varchar(32) DEFAULT NULL COMMENT '分类',
              `hourNum_date` varchar(32) DEFAULT NULL COMMENT '日期',
              `hourNum_hour` varchar(2) DEFAULT NULL COMMENT '小时',
              `hourNum_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='24小时访问量统计'";
            
            // IP统计
            $huoma_ip = "CREATE TABLE `huoma_ip` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `qun_ip` int(10) NOT NULL DEFAULT '0' COMMENT '群活码IP访问次数',
              `kf_ip` int(10) NOT NULL DEFAULT '0' COMMENT '客服码IP访问次数',
              `channel_ip` int(10) NOT NULL DEFAULT '0' COMMENT '渠道码IP访问次数',
              `dwz_ip` int(10) NOT NULL DEFAULT '0' COMMENT '短网址IP访问次数',
              `zjy_ip` int(10) NOT NULL DEFAULT '0' COMMENT '中间页IP访问次数',
              `shareCard_ip` int(10) NOT NULL DEFAULT '0' COMMENT '分享卡片IP访问次数',
              `multiSPA_ip` int(10) NOT NULL DEFAULT '0' COMMENT '多项单页IP访问次数',
              `ip_create_time` varchar(32) DEFAULT NULL COMMENT 'IP记录日期'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='IP统计'";
            
            // IP临时记录
            $huoma_ip_temp = "CREATE TABLE `huoma_ip_temp` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `ip` varchar(32) DEFAULT NULL COMMENT 'IP地址',
              `create_date` varchar(32) DEFAULT NULL COMMENT '访问日期',
              `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '访问时间',
              `from_page` varchar(32) DEFAULT NULL COMMENT '来自的页面'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='IP临时记录'";
            
            // 通知渠道配置
            $huoma_notification = "CREATE TABLE `huoma_notification` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `corpid` varchar(64) DEFAULT NULL COMMENT '企业微信corpid',
              `corpsecret` varchar(64) DEFAULT NULL COMMENT '企业微信corpsecret',
              `touser` varchar(32) DEFAULT NULL COMMENT '企业微信接收者ID',
              `agentid` varchar(32) DEFAULT NULL COMMENT '企业微信应用ID',
              `bark_url` text DEFAULT NULL COMMENT 'bark推送链接',
              `email_acount` text DEFAULT NULL COMMENT '邮件发送端账号',
              `email_pwd` text DEFAULT NULL COMMENT '邮件发送端授权码',
              `email_smtp` varchar(64) DEFAULT NULL COMMENT '邮件服务器',
              `email_port` varchar(32) DEFAULT NULL COMMENT '邮件服务器端口',
              `email_receive` text DEFAULT NULL COMMENT '接收邮件的邮箱',
              `SendKey` text DEFAULT NULL COMMENT 'Server酱SendKey',
              `http_url` text DEFAULT NULL COMMENT '接收POST数据的URL'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知渠道配置'";
            
            // 添加默认通知渠道配置
            $huoma_notification_Data = "INSERT INTO `huoma_notification` (
            `corpid`,`corpsecret`,`touser`,`agentid`,
            `bark_url`,`email_acount`,`email_pwd`,`email_smtp`,
            `email_port`,`email_receive`,`SendKey`,`http_url`) VALUES (
            '未设置','未设置','未设置','未设置',
            '未设置','未设置','未设置','未设置',
            '未设置','未设置','未设置','未设置')";
            
            // 素材库
            $huoma_sucai = "CREATE TABLE `huoma_sucai` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `sucai_id` int(10) DEFAULT NULL COMMENT '素材ID',
              `sucai_filename` text DEFAULT NULL COMMENT '文件名',
              `sucai_beizhu` text DEFAULT NULL COMMENT '素材备注',
              `sucai_upload_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '上传时间',
              `sucai_upload_user` varchar(32) DEFAULT NULL COMMENT '上传用户',
              `sucai_type` int(10) DEFAULT NULL COMMENT '素材类型（1图片 2文档）',
              `sucai_size` int(10) DEFAULT NULL COMMENT '素材大小'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='素材库'";
            
            // 多项单页
            $huoma_tbk_mutiSPA = "CREATE TABLE `huoma_tbk_mutiSPA` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `multiSPA_id` int(10) DEFAULT NULL COMMENT '单页ID',
              `multiSPA_title` varchar(64) DEFAULT NULL COMMENT '单页标题',
              `multiSPA_rkym` text DEFAULT NULL COMMENT '入口域名',
              `multiSPA_ldym` text DEFAULT NULL COMMENT '落地域名',
              `multiSPA_dlym` text DEFAULT NULL COMMENT '短链域名',
              `multiSPA_key` varchar(10) DEFAULT NULL COMMENT '短网址Key',
              `multiSPA_project` text DEFAULT NULL COMMENT '项目HTML',
              `multiSPA_img` text DEFAULT NULL COMMENT '主图Url',
              `multiSPA_pv` int(10) DEFAULT '0' COMMENT '访问量',
              `multiSPA_addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `create_user` varchar(32) DEFAULT NULL COMMENT '创建用户'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='多项单页'";
            
            // 用户组表
            $ylb_usergroup = "CREATE TABLE `ylb_usergroup` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `usergroup_id` int(10) DEFAULT NULL COMMENT '用户组ID',
              `usergroup_name` varchar(32) DEFAULT NULL COMMENT '用户组名称',
              `navList` TEXT DEFAULT NULL COMMENT '授权页面'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户组'";
            
            // [超管] 授权页面
            $navList1 = '[{"href":"../index/","icon":"i-data","text":"数据"},{"href":"../qun/","icon":"i-hm","text":"活码"},{"href":"../dwz/","icon":"i-dwz","text":"短网址"},{"href":"../tbk/","icon":"i-tbk","text":"淘宝客"},{"href":"../shareCard/","icon":"i-share","text":"分享卡片"},{"href":"../plugin/","icon":"i-plugin","text":"插件中心"},{"href":"../kami/","icon":"i-kami","text":"卡密分发"},{"href":"../config/","icon":"i-config","text":"配置中心"},{"href":"../sucai/","icon":"i-sucai","text":"素材管理"},{"href":"../user/","icon":"i-account","text":"账号管理"}]';
            
            // [会员] 授权页面
            $navList2 = '[{"href":"../qun/","icon":"i-hm","text":"活码"},{"href":"../dwz/","icon":"i-dwz","text":"短网址"},{"href":"../tbk/","icon":"i-tbk","text":"淘宝客"},{"href":"../plugin/","icon":"i-plugin","text":"插件中心"},{"href":"../sucai/","icon":"i-sucai","text":"素材管理"},{"href":"../user/","icon":"i-account","text":"账号管理"}]';
            
            // 添加一个 [超管] 用户组
            $ylb_usergroup_data1 = "INSERT INTO `ylb_usergroup` (`usergroup_id`, `usergroup_name`, `navList`) VALUES (100000, '超管', '$navList1')";
            
            // 添加一个 [会员] 用户组
            $ylb_usergroup_data2 = "INSERT INTO `ylb_usergroup` (`usergroup_id`, `usergroup_name`, `navList`) VALUES (100001, '会员', '$navList2')";
            
            // 卡密项目表
            $ylb_kami = "CREATE TABLE `ylb_kami` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kami_id` int(10) DEFAULT NULL COMMENT '卡密项目ID',
              `kami_title` varchar(64) DEFAULT NULL COMMENT '卡密项目标题',
              `kami_type` varchar(32) DEFAULT NULL COMMENT '卡密类型',
              `km_total` int(10) DEFAULT '0' COMMENT '卡密总数',
              `km_isExtracted` int(10) NOT NULL DEFAULT '0' COMMENT '已被提取',
              `km_unExtracted` int(10) DEFAULT '0' COMMENT '未被提取',
              `kami_repeat_tiqu` int(10) DEFAULT '2' COMMENT '重复提取（1允许 2不允许）',
              `kami_repeat_tiqu_interval` int(10) DEFAULT NULL COMMENT '重复提取间隔时间（单位：秒）',
              `kami_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `kami_status` int(1) NOT NULL DEFAULT '1' COMMENT '状态',
              `kami_adStatus` int(1) NOT NULL DEFAULT '2' COMMENT '是否需看广告1是 2否',
              `kami_key` varchar(32) DEFAULT NULL COMMENT 'Key',
              `kami_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            // 卡密列表
            $ylb_kmlist = "CREATE TABLE `ylb_kmlist` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kami_id` int(10) DEFAULT NULL COMMENT '绑定的卡密项目ID',
              `km_id` int(10) DEFAULT NULL COMMENT '卡密id',
              `km` varchar(64) DEFAULT NULL COMMENT '卡密',
              `km_expiryDate` varchar(32) DEFAULT NULL COMMENT '有效期',
              `km_expireDate` varchar(32) DEFAULT NULL COMMENT '到期时间',
              `km_addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '导入时间',
              `km_beizhu` varchar(255) DEFAULT NULL COMMENT '备注',
              `km_status` int(1) NOT NULL DEFAULT '1' COMMENT '状态',
              `km_addUser` varchar(32) DEFAULT NULL COMMENT '操作用户'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            // 卡密系统配置
            $ylb_kamiConfig = "CREATE TABLE `ylb_kamiConfig` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kmConf_status` int(1) NOT NULL DEFAULT '1' COMMENT '服务状态',
              `kmConf_xcx_title` varchar(64) DEFAULT NULL COMMENT '提取页顶部标题',
              `kmConf_adShow` int(1) NOT NULL DEFAULT '2' COMMENT '提取页广告开关',
              `kmConf_adType` int(1) NOT NULL DEFAULT '1' COMMENT '提取页广告类型',
              `kmConf_btntext` varchar(32) DEFAULT '看广告免费提取' COMMENT '提取按钮文字',
              `kmConf_jiliStatus` int(1) DEFAULT '2' COMMENT '激励视频广告开关',
              `kmConf_kfQrcode` text COMMENT '客服二维码',
              `kmConf_notification_text` text COMMENT '公告内容',
              `kmConf_appid` varchar(32) DEFAULT NULL COMMENT '小程序AppId',
              `kmConf_appsecret` varchar(64) DEFAULT NULL COMMENT '小程序AppSecret'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            // 卡密系统配置，添加默认配置 
            $ylb_kamiConfig_default = "INSERT INTO `ylb_kamiConfig` (`kmConf_xcx_title`) VALUES ('提取页')";
            
            // 提取记录
            $ylb_km_openid = "CREATE TABLE `ylb_km_openid` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kami_id` int(10) DEFAULT NULL COMMENT '卡密项目ID',
              `kami_title` varchar(255) DEFAULT NULL COMMENT '卡密项目标题',
              `openid` varchar(64) DEFAULT NULL COMMENT 'openid',
              `km` varchar(255) NOT NULL COMMENT '卡密',
              `km_expiryDate` varchar(32) DEFAULT NULL COMMENT '卡密有效期',
              `tiqu_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '提取时间',
              `brand` varchar(32) DEFAULT NULL COMMENT '设备品牌',
              `model` varchar(32) DEFAULT NULL COMMENT '设备型号',
              `system` varchar(32) DEFAULT NULL COMMENT '操作系统及版本',
              `status` int(1) NOT NULL DEFAULT '1' COMMENT 'status'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            // openid封禁名单
            $ylb_km_openid_ban = "CREATE TABLE `ylb_km_openid_ban` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kami_id` int(10) DEFAULT NULL COMMENT '卡密项目ID',
              `openid` varchar(64) DEFAULT NULL COMMENT 'openid',
              `ban_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '封禁时间',
              `status` int(1) NOT NULL DEFAULT '1' COMMENT 'status'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            // 连接成功
            // 检查是否已经安装
            // 1、检查是否存在Db.php或安装锁
            if(file_exists('./install.lock') || file_exists('../console/Db.php')){
                
                // 存在Db.php
                // 已安装
                $result = array(
                    'code' => 202,
                    'msg' => '请勿重复安装！如需重新安装请删除 /install/install.lock 和 /console/Db.php'
                );
            }else{
                
                // 不存在Db.php
                // 开始创建表
                if(
                    $conn->query($huoma_user) === TRUE &&
                    $conn->query($huoma_user_Data) === TRUE &&
                    $conn->query($huoma_qun) === TRUE &&
                    $conn->query($huoma_qun_zima) === TRUE &&
                    $conn->query($ylb_qun_bingliu) === TRUE &&
                    $conn->query($huoma_kf) === TRUE && 
                    $conn->query($huoma_kf_zima) === TRUE &&
                    $conn->query($huoma_domain) === TRUE &&
                    $conn->query($huoma_domain_Data) === TRUE &&
                    $conn->query($huoma_channel) === TRUE &&
                    $conn->query($huoma_channel_data) === TRUE &&
                    $conn->query($huoma_channel_accessdenied) === TRUE &&
                    $conn->query($huoma_dwz) === TRUE &&
                    $conn->query($huoma_dwz_apikey) === TRUE &&
                    $conn->query($huoma_tbk) === TRUE &&
                    $conn->query($huoma_tbk_config) === TRUE &&
                    $conn->query($huoma_shareCard) === TRUE &&
                    $conn->query($huoma_shareCardConfig) === TRUE &&
                    $conn->query($huoma_domainCheck) === TRUE &&
                    $conn->query($huoma_domainCheck_Data) === TRUE &&
                    $conn->query($huoma_hourNum) === TRUE &&
                    $conn->query($huoma_ip) === TRUE &&
                    $conn->query($huoma_ip_temp) === TRUE &&
                    $conn->query($huoma_notification) === TRUE &&
                    $conn->query($huoma_notification_Data) === TRUE &&
                    $conn->query($huoma_sucai) === TRUE &&
                    $conn->query($huoma_tbk_mutiSPA) === TRUE &&
                    $conn->query($ylb_usergroup) === TRUE &&
                    $conn->query($ylb_usergroup_data1) === TRUE &&
                    $conn->query($ylb_usergroup_data2) === TRUE &&
                    $conn->query($ylb_kami) === TRUE &&
                    $conn->query($ylb_kmlist) === TRUE &&
                    $conn->query($ylb_kamiConfig) === TRUE &&
                    $conn->query($ylb_kamiConfig_default) === TRUE &&
                    $conn->query($ylb_km_openid) === TRUE &&
                    $conn->query($ylb_km_openid_ban) === TRUE){

                    // 淘宝客配置默认数据
                    $huoma_tbk_config_Data = "INSERT INTO `huoma_tbk_config` (`zjy_config_appkey`, `zjy_config_sid`, `zjy_config_pid`, `zjy_config_tbname`, `zjy_config_user`) VALUES ('未设置', '未设置', '未设置', '未设置', '$user_name')";
                    $conn->query($huoma_tbk_config_Data);
                    
                    // 分享卡片配置默认数据
                    $huoma_shareCardConfig_Data = "INSERT INTO `huoma_shareCardConfig` (`appid`, `appsecret`) VALUES ('未设置', '未设置')";
                    $conn->query($huoma_shareCardConfig_Data);
                    
                    // 生成数据库配置文件
                    $Db_config = [
                        'db_host' => $db_host,
                        'db_port' => 3306,
                        'db_name' => $db_name,
                        'db_user' => $db_user,
                        'db_pass' => $db_pass,
                        'db_prefix' => '',
                        'folderNum' => $folder,
                        'version' => '2.4.6'
                    ];
                    
                    // 生成Db.php文件内容
                    $fileContent = "<?php\n\n";
                    $fileContent .= "// 数据库操作类\n";
                    $fileContent .= "include 'DbClass.php';\n\n";
                    $fileContent .= "// 数据库配置\n";
                    $fileContent .= '$config = ' . var_export($Db_config, true) . ";\n";
                    $fileContent .= "?>";
                    
                    // 将内容写入Db.php文件
                    $filePath = "../console/Db.php";
                    file_put_contents($filePath, $fileContent);
                    
                    // 创建安装锁
                    file_put_contents('./install.lock','安装锁');
                    
                    // 安装成功
                    $result = array(
                        'code' => 200,
                        'msg' => '安装成功',
                        'current_time' => date('Y-m-d H:i:s'),
                        'client_ip' => $_SERVER['REMOTE_ADDR'],
                        'server_ip' => $_SERVER['SERVER_ADDR'],
                        'user_email' => $user_email
                    );
                    
                }else{
                    
                    // 安装失败
                    $result = array(
                        'code' => 202,
                        'msg' => '安装失败：' . $conn->error
                    );
                }
            }
            
        }
    }
    
    // 输出JSON
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	
?>
