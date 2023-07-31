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
	
	// 数据库配置
    include '../console/Db.php';
	
    // 连接数据库
	$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
	
	// 验证数据库的连接
	if ($conn->connect_error) {
	    
	   $result = array(
	       'code' => 202,
	       'msg' => '数据库连接失败'.$conn->connect_error
	   );
	   echo json_encode($result,JSON_UNESCAPED_UNICODE);
	   die();
    }
    
    // 今天日期
    $nowDate = date('Y-m-d');
    
    // 初始IP访问量
    $initIP = '{"pv":"0","date":"'.$nowDate.'"}';
    
    // 获取http协议类型
    $HTTP_TYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    
    // 1、在huoma_qun表中添加以下字段
    $huoma_qun = "ALTER TABLE huoma_qun 
        ADD qun_today_pv varchar(32) DEFAULT NULL COMMENT '今天访问量',
        ADD qun_notify varchar(32) DEFAULT NULL COMMENT '通知渠道'";
    
    // 更新qun_today_pv默认数据
    $huoma_qun_Data = "UPDATE `huoma_qun` SET `qun_today_pv` = '$initIP'";
    
    // 2、在huoma_kf表中添加以下字段
    $huoma_kf = "ALTER TABLE huoma_kf 
        ADD kf_today_pv varchar(32) DEFAULT NULL COMMENT '今天的访问量',
        ADD kf_onlinetimes text COMMENT '在线时间JSON配置'";
        
    // 更新kf_today_pv默认数据
    $huoma_kf_Data = "UPDATE `huoma_kf` SET `kf_today_pv` = '$initIP'";
    
    // kf_onlinetimes配置
    $kf_onlinetimes_jsonStr = 
    '{
        "1": {
            "morning": "09:00-12:00",
            "afternoon": "14:00-18:00",
            "evening": "20:00-22:00"
        },
        "2": {
            "morning": "09:00-12:00",
            "afternoon": "14:00-18:00",
            "evening": "20:00-22:00"
        },
        "3": {
            "morning": "09:00-12:00",
            "afternoon": "14:00-18:00",
            "evening": "20:00-22:00"
        },
        "4": {
            "morning": "09:00-12:00",
            "afternoon": "14:00-18:00",
            "evening": "20:00-22:00"
        },
        "5": {
            "morning": "09:00-12:00",
            "afternoon": "14:00-18:00",
            "evening": "20:00-22:00"
        },
        "6": {
            "morning": "00:00-00:00",
            "afternoon": "00:00-00:00",
            "evening": "00:00-00:00"
        },
        "7": {
            "morning": "00:00-00:00",
            "afternoon": "00:00-00:00",
            "evening": "00:00-00:00"
        }
    }';
    
    // 更新kf_onlinetimes默认数据
    $kf_onlinetimes_Data = "UPDATE `huoma_kf` SET `kf_onlinetimes` = '$kf_onlinetimes_jsonStr'";
    
    // 3、在huoma_channel表中添加以下字段
    $huoma_channel = "ALTER TABLE huoma_channel 
        ADD channel_today_pv varchar(32) DEFAULT NULL COMMENT '今天的访问量'";
    
    // 更新channel_today_pv默认数据
    $huoma_channel_Data = "UPDATE `huoma_channel` SET `channel_today_pv` = '$initIP'";
    
    // 4、在huoma_dwz表中添加以下字段
    $huoma_dwz = "ALTER TABLE huoma_dwz 
        ADD dwz_today_pv varchar(32) DEFAULT NULL COMMENT '今天访问量',
        ADD dwz_android_url text DEFAULT NULL COMMENT 'Android设备目标链接',
        ADD dwz_ios_url text DEFAULT NULL COMMENT 'iOS设备目标链接',
        ADD dwz_windows_url text DEFAULT NULL COMMENT 'Windows设备目标链接'";
        
    // 更新dwz_today_pv默认数据
    $huoma_dwz_Data = "UPDATE `huoma_dwz` SET `dwz_today_pv` = '$initIP'";
    
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
      `email_port` varchar(5) DEFAULT NULL COMMENT '邮件服务器端口',
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
      `sucai_type` int(10) DEFAULT NULL COMMENT '素材类型（1图片 2视频 3音频）',
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
    
    if (
        $conn->query($huoma_qun) === TRUE && 
        $conn->query($huoma_kf) === TRUE && 
        $conn->query($huoma_channel) === TRUE && 
        $conn->query($huoma_dwz) === TRUE && 
        $conn->query($huoma_domainCheck) === TRUE && 
        $conn->query($huoma_domainCheck_Data) === TRUE && 
        $conn->query($huoma_hourNum) === TRUE && 
        $conn->query($huoma_ip) === TRUE && 
        $conn->query($huoma_ip_temp) === TRUE && 
        $conn->query($huoma_notification) === TRUE && 
        $conn->query($huoma_notification_Data) === TRUE && 
        $conn->query($huoma_sucai) === TRUE && 
        $conn->query($huoma_tbk_mutiSPA) === TRUE && 
        $conn->query($huoma_qun_Data) === TRUE && 
        $conn->query($huoma_kf_Data) === TRUE && 
        $conn->query($kf_onlinetimes_Data) === TRUE && 
        $conn->query($huoma_channel_Data) === TRUE && 
        $conn->query($huoma_dwz_Data) === TRUE) {
            
        // 休息5秒
        sleep(5);
        
        // 升级成功
        $result = array(
            'code' => 200,
            'msg' => '升级完成！'
        );
        
    }else if(preg_match("/Duplicate column/", $conn->error)){
        
        // 存在相同的字段
        $result = array(
            'code' => 202,
            'msg' => '升级过程中发现数据库存在相同的字段！请根据报错信息：（'.$conn->error.'）排查问题！'
        );
    }else if(preg_match("/already exists/", $conn->error)){
        
        // 存在huoma_前缀的表
        $result = array(
            'code' => 202,
            'msg' => '升级过程中发现数据库存在相同的表！请根据报错信息：（'.$conn->error.'）排查问题！'
        );
    }else{
        
        // 升级失败
        $result = array(
            'code' => 202,
            'msg' => '升级失败！请根据报错信息：（'.$conn->error.'）排查问题！'
        );
    }
    
    // 关闭数据库连接
    $conn->close();
    
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>