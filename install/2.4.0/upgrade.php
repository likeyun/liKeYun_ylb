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
	
    // ylb_qun_bingliu（新建表）
    $ylb_qun_bingliu = "CREATE TABLE `ylb_qun_bingliu` (
      `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
      `bingliu_id` int(10) DEFAULT NULL COMMENT '并流ID',
      `before_qun_id` int(10) DEFAULT NULL COMMENT '原活码ID',
      `later_qun_id` int(10) DEFAULT NULL COMMENT '并入活码ID',
      `bingliu_num` int(10) NOT NULL DEFAULT '0' COMMENT '并流次数',
      `bingliu_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1开 2关）',
      `createUser` varchar(32) DEFAULT NULL COMMENT '操作用户'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群活码下的群二维码'";
    
    // huoma_channel（新增字段）
    $ALTER_huoma_channel = "ALTER TABLE `huoma_channel` 
    ADD `Android_Total` int(10) DEFAULT '0' COMMENT 'Android设备数据量',
    ADD `iOS_Total` int(10) DEFAULT '0' COMMENT 'iOS设备数据量',
    ADD `Windows_Total` int(10) DEFAULT '0' COMMENT 'Windows设备数据量',
    ADD `Linux_Total` int(10) DEFAULT '0' COMMENT 'Linux设备数据量',
    ADD `MacOS_Total` int(10) DEFAULT '0' COMMENT 'MacOS设备数据量',
    ADD `channel_DataTotal` int(10) DEFAULT '0' COMMENT '数据量'";
    
    // huoma_dwz（新增字段）
    $ALTER_huoma_dwz = "ALTER TABLE `huoma_dwz` ADD `dwz_lxymStatus` int(1) NOT NULL DEFAULT '2' COMMENT '轮询域名启用状态'";
    
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
      `kmConf_bannerID` varchar(32) DEFAULT NULL COMMENT 'Banner广告ID',
      `kmConf_videoID` varchar(32) DEFAULT NULL COMMENT 'video广告ID',
      `kmConf_jiliStatus` int(1) DEFAULT '2' COMMENT '激励视频广告开关',
      `kmConf_jiliID` varchar(32) DEFAULT NULL COMMENT '激励视频广告ID',
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
    
    // 数据库配置
    include '../../console/Db.php';
	
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
    
    // 验证以上语句是否执行成功
    if (
        $conn->query($ylb_qun_bingliu) === TRUE && 
        $conn->query($ALTER_huoma_channel) === TRUE && 
        $conn->query($ALTER_huoma_dwz) === TRUE && 
        $conn->query($ylb_kami) === TRUE && 
        $conn->query($ylb_kmlist) === TRUE && 
        $conn->query($ylb_kamiConfig) === TRUE && 
        $conn->query($ylb_kamiConfig_default) === TRUE && 
        $conn->query($ylb_km_openid) === TRUE &&
        $conn->query($ylb_km_openid_ban) === TRUE){
        
        // 以上所有执行成功
        // 读取数据库配置文件
        $fileContents = file_get_contents('../../console/Db.php');
        
        // 新的版本号
        $newVersion = '2.4.0';
        
        // 使用正则表达式替换旧版本号
        $updatedContents = preg_replace("/'version' => '[^']*'/", "'version' => '$newVersion'", $fileContents);
        
        // 将更新后的内容写回文件
        file_put_contents('../../console/Db.php', $updatedContents);
        
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
        
        // 存在相同的表
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
	
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>