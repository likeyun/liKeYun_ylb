<?php
    
    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     */

	// 页面编码
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
    $install_folder = trim($_POST['install_folder']);
    
    // 过滤参数
    if(empty($db_host) || !isset($db_host)){
        
        $result = array(
            'code' => 203,
            'msg' => '数据库地址未填写'
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
    }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_name)){
    
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
    }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
    
        $result = array(
		    'code' => 203,
            'msg' => '密码不能存在特殊字符'
	    );
    }else if(empty($install_folder) || !isset($install_folder)){
        
        $result = array(
            'code' => 203,
            'msg' => '安装目录级别未选择'
        );
    }else{
        
        // 验证数据库地址、账号、密码
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        // 根据数据库连接返回的错误信息判断连接失败的原因
        if($conn->connect_error == 'Connection timed out'){
            
            // 连接超时
            $result = array(
                'code' => 202,
                'msg' => '连接超时，可能是数据库地址有误'
            );
        }else if(preg_match("/getaddrinfo failed/", $conn->connect_error)){
            
            // 数据库地址有误
            $result = array(
                'code' => 202,
                'msg' => '数据库地址有误'
            );
        }else if(preg_match("/using password/", $conn->connect_error)){
            
            // 数据库账号或密码有误
            $result = array(
                'code' => 202,
                'msg' => '数据库账号或密码有误'
            );
        }else{
            
            $huoma_user = "CREATE TABLE `huoma_user` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `user_id` int(10) DEFAULT NULL COMMENT '用户ID',
              `user_name` varchar(32) DEFAULT NULL COMMENT '账号',
              `user_pass` varchar(64) DEFAULT NULL COMMENT '密码',
              `user_email` text COMMENT '邮箱',
              `user_mb_ask` text COMMENT '密保问题',
              `user_mb_answer` varchar(32) DEFAULT NULL COMMENT '密保答案',
              `user_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
              `user_admin` int(2) DEFAULT '2' COMMENT '管理权限（1是 2否）',
              `user_manager` varchar(32) DEFAULT NULL COMMENT '账号管理者',
              `user_beizhu` varchar(64) DEFAULT NULL COMMENT '备注信息',
              `user_status` int(2) NOT NULL DEFAULT '1' COMMENT '账号状态（1可用 2停用）'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $huoma_user_Data = "INSERT INTO `huoma_user` (`user_id`, `user_name`, `user_pass`, `user_email`, `user_mb_ask`, `user_admin`, `user_manager`, `user_beizhu`) VALUES (100000, '".$user_name."', '".MD5($user_pass)."', '".$user_email."','请选择密保问题', 1, '".$user_name."', '超级管理员')";
            
            $huoma_qun = "CREATE TABLE `huoma_qun` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `qun_id` int(10) DEFAULT NULL COMMENT '群ID',
              `qun_title` varchar(64) DEFAULT NULL COMMENT '群标题',
              `qun_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开启 2关闭）默认1',
              `qun_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `qun_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `qun_qc` int(2) NOT NULL DEFAULT '2' COMMENT '去重（1开启 2关闭）默认2',
              `qun_rkym` text COMMENT '入口域名',
              `qun_ldym` text COMMENT '落地域名',
              `qun_dlym` text COMMENT '短链域名',
              `qun_kf` text COMMENT '客服二维码',
              `qun_kf_status` int(2) NOT NULL DEFAULT '2' COMMENT '客服开启状态（1开启 2关闭）默认2',
              `qun_safety` int(2) NOT NULL DEFAULT '1' COMMENT '顶部安全提示（1显 2隐）',
              `qun_beizhu` text COMMENT '群备注',
              `qun_key` varchar(10) DEFAULT NULL COMMENT '短链接Key',
              `qun_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群活码列表'";
            
            $huoma_qun_zima = "CREATE TABLE `huoma_qun_zima` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `qun_id` int(10) DEFAULT NULL COMMENT '群活码ID',
              `zm_id` int(10) DEFAULT NULL COMMENT '群子码ID',
              `zm_yz` int(10) NOT NULL DEFAULT '0' COMMENT '阈值',
              `zm_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `zm_qrcode` text COMMENT '二维码URL',
              `zm_leader` varchar(32) DEFAULT NULL COMMENT '群主微信号',
              `zm_update_time` varchar(32) DEFAULT NULL COMMENT '更新时间',
              `zm_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开 2关）'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $huoma_kf = "CREATE TABLE `huoma_kf` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kf_id` int(10) DEFAULT NULL COMMENT '客服码ID',
              `kf_title` varchar(64) DEFAULT NULL COMMENT '客服标题或昵称',
              `kf_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `kf_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `kf_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `kf_rkym` text COMMENT '入口域名',
              `kf_ldym` text COMMENT '落地域名',
              `kf_dlym` text COMMENT '短链域名',
              `kf_model` int(2) DEFAULT NULL COMMENT '展示模式（1阈值 2随机）',
              `kf_online` int(2) NOT NULL DEFAULT '2' COMMENT '在线状态（1显 2隐）',
              `kf_key` varchar(10) DEFAULT NULL COMMENT '短链接Key',
              `kf_safety` int(2) NOT NULL DEFAULT '1' COMMENT '顶部安全提示（1显 2隐）',
              `kf_beizhu` text COMMENT '备注',
              `kf_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $huoma_kf_zima = "CREATE TABLE `huoma_kf_zima` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kf_id` int(10) DEFAULT NULL COMMENT '客服码ID',
              `zm_id` int(10) DEFAULT NULL COMMENT '子码ID',
              `zm_yz` int(10) DEFAULT '0' COMMENT '阈值',
              `zm_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `zm_qrcode` text COMMENT '二维码URL',
              `zm_num` varchar(32) DEFAULT NULL COMMENT '客服微信号',
              `zm_update_time` varchar(32) DEFAULT NULL COMMENT '更新时间',
              `zm_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开 2关）	'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $huoma_domain = "CREATE TABLE `huoma_domain` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `domain_id` int(10) DEFAULT NULL COMMENT '域名ID',
              `domain_type` int(2) DEFAULT NULL COMMENT '域名类型（1入口 2落地 3短链）',
              `domain` text COMMENT '域名'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $HTTP_TYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            
            $huoma_domain_Data = "INSERT INTO `huoma_domain` (`domain_id`, `domain_type`, `domain`) VALUES
            (100000, 1, '".$HTTP_TYPE.$_SERVER['HTTP_HOST']."'),
            (100001, 2, '".$HTTP_TYPE.$_SERVER['HTTP_HOST']."'),
            (100002, 3, '".$HTTP_TYPE.$_SERVER['HTTP_HOST']."')";
            
            $huoma_count = "CREATE TABLE `huoma_count` (
              `id` int(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID（月份）',
              `count_date` date DEFAULT NULL COMMENT '日期',
              `count_hour` int(2) DEFAULT NULL COMMENT '小时',
              `count_qun_pv` int(10) NOT NULL DEFAULT '0' COMMENT '群活码',
              `count_kf_pv` int(10) NOT NULL DEFAULT '0' COMMENT '客服码',
              `count_channel_pv` int(10) NOT NULL DEFAULT '0' COMMENT '渠道码'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $huoma_count_Data = "INSERT INTO `huoma_count` (`id`, `count_date`, `count_hour`, `count_qun_pv`, `count_kf_pv`, `count_channel_pv`) VALUES
            (1, '".date('Y-m-d')."', 1, 0, 0, 0),
            (2, '".date('Y-m-d')."', 2, 0, 0, 0),
            (3, '".date('Y-m-d')."', 3, 0, 0, 0),
            (4, '".date('Y-m-d')."', 4, 0, 0, 0),
            (5, '".date('Y-m-d')."', 5, 0, 0, 0),
            (6, '".date('Y-m-d')."', 6, 0, 0, 0),
            (7, '".date('Y-m-d')."', 7, 0, 0, 0),
            (8, '".date('Y-m-d')."', 8, 0, 0, 0),
            (9, '".date('Y-m-d')."', 9, 0, 0, 0),
            (10, '".date('Y-m-d')."', 10, 0, 0, 0),
            (11, '".date('Y-m-d')."', 11, 0, 0, 0),
            (12, '".date('Y-m-d')."', 12, 0, 0, 0),
            (13, '".date('Y-m-d')."', 13, 0, 0, 0),
            (14, '".date('Y-m-d')."', 14, 0, 0, 0),
            (15, '".date('Y-m-d')."', 15, 0, 0, 0),
            (16, '".date('Y-m-d')."', 16, 0, 0, 0),
            (17, '".date('Y-m-d')."', 17, 0, 0, 0),
            (18, '".date('Y-m-d')."', 18, 0, 0, 0),
            (19, '".date('Y-m-d')."', 19, 0, 0, 0),
            (20, '".date('Y-m-d')."', 20, 0, 0, 0),
            (21, '".date('Y-m-d')."', 21, 0, 0, 0),
            (22, '".date('Y-m-d')."', 22, 0, 0, 0),
            (23, '".date('Y-m-d')."', 23, 0, 0, 0)";
            
            $huoma_channel = "CREATE TABLE `huoma_channel` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `channel_id` int(10) DEFAULT NULL COMMENT '渠道ID',
              `channel_title` varchar(64) DEFAULT NULL COMMENT '渠道标题',
              `channel_status` int(2) DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `channel_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `channel_pv` int(10) DEFAULT '0' COMMENT '访问量',
              `channel_rkym` text COMMENT '入口域名',
              `channel_ldym` text COMMENT '落地域名',
              `channel_dlym` text COMMENT '短链域名',
              `channel_key` varchar(10) DEFAULT NULL COMMENT '短链接key',
              `channel_url` text COMMENT '渠道目标链接',
              `channel_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $huoma_channel_data = "CREATE TABLE `huoma_channel_data` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `data_id` int(10) DEFAULT NULL COMMENT '数据ID',
              `channel_id` int(10) DEFAULT NULL COMMENT '渠道ID',
              `data_referer` text COMMENT '数据来源',
              `data_device` varchar(32) DEFAULT NULL COMMENT '来源设备',
              `data_ip` varchar(32) DEFAULT NULL COMMENT '数据来源IP',
              `data_pv` int(10) DEFAULT '0' COMMENT '访问量',
              `data_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '数据来源的时间'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $huoma_channel_accessdenied = "CREATE TABLE `huoma_channel_accessdenied` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `data_ip` varchar(32) DEFAULT NULL COMMENT 'IP',
              `accessdenied_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入时间',
              `add_user` varchar(32) DEFAULT NULL COMMENT '操作者账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            // 连接成功
            // 检查是否已经安装
            // （1）检查是否存在Db.php
            if(file_exists('../console/Db.php')){
                
                // 存在Db.php
                // 已安装
                $result = array(
                    'code' => 202,
                    'msg' => '请勿重复安装！如需重新安装请删除 /console/Db.php'
                );
            }else{
                
                // 不存在Db.php
                // 开始创建表
                if(
                    $conn->query($huoma_user) === TRUE && 
                    $conn->query($huoma_user_Data) === TRUE && 
                    $conn->query($huoma_qun) === TRUE && 
                    $conn->query($huoma_qun_zima) === TRUE && 
                    $conn->query($huoma_kf) === TRUE && 
                    $conn->query($huoma_kf_zima) === TRUE && 
                    $conn->query($huoma_domain) === TRUE && 
                    $conn->query($huoma_domain_Data) === TRUE && 
                    $conn->query($huoma_count) === TRUE && 
                    $conn->query($huoma_count_Data) === TRUE && 
                    $conn->query($huoma_channel) === TRUE && 
                    $conn->query($huoma_channel_data) === TRUE && 
                    $conn->query($huoma_channel_accessdenied) === TRUE){

                    // 创建数据库配置文件
                    $Db_Config_File = '<?php'.PHP_EOL.PHP_EOL.'// 数据库操作类'.PHP_EOL.'include \'DbClass.php\';'.PHP_EOL.PHP_EOL.'// 数据库配置'.PHP_EOL.'$config = ['.PHP_EOL.'    \'db_host\' => \''.$db_host.'\','.PHP_EOL.'    \'db_port\' => 3306,'.PHP_EOL.'    \'db_name\' => \''.$db_name.'\','.PHP_EOL.'    \'db_user\' => \''.$db_user.'\','.PHP_EOL.'    \'db_pass\' => \''.$db_pass.'\','.PHP_EOL.'    \'folderNum\' => \''.$install_folder.'\''.PHP_EOL.'];'.PHP_EOL.'?>';
                    file_put_contents('../console/Db.php',$Db_Config_File);
                    
                    // 安装成功
                    $result = array(
                        'code' => 200,
                        'msg' => '安装成功'
                    );
                    
                }else if(preg_match("/already exists/", $conn->error)){
                    
                    // 存在huoma_前缀的表
                    $result = array(
                        'code' => 202,
                        'msg' => '请勿重复安装！如需重新安装请删除huoma_前缀的表！'
                    );
                }else{
                    
                    // 安装失败
                    $result = array(
                        'code' => 202,
                        'msg' => '安装失败，报错信息：'.$conn->error
                    );
                }
            }
            
        }
    }
    
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>