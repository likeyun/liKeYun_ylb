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
    
    // 用户组表
    $ylb_usergroup = "CREATE TABLE `ylb_usergroup` (
      `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
      `usergroup_id` int(10) DEFAULT NULL COMMENT '用户组ID',
      `usergroup_name` varchar(32) DEFAULT NULL COMMENT '用户组名称'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户组'";
    
    // 添加一个默认用户组
    $ylb_usergroup_Data = "INSERT INTO `ylb_usergroup` (`usergroup_id`, `usergroup_name`) VALUES (100000, '默认')";
    
    // 给huoma_user添加一个user_group字段
    $ALTER_user_group = "ALTER TABLE `huoma_user` ADD `user_group` VARCHAR(32) DEFAULT NULL COMMENT '用户组'";
    
    // 设置所有账号的用户组为默认
    $set_all_user_group = "UPDATE huoma_user SET user_group='默认'";
    
    // 给huoma_domain添加一个domain_usergroup字段
    $ALTER_domain_usergroup = "ALTER TABLE `huoma_domain` ADD `domain_usergroup` VARCHAR(255) DEFAULT NULL COMMENT '授权用户组'";
    
    // 设置所有域名的授权用户组添加一个默认
    $set_all_domain_usergroup = "UPDATE huoma_domain SET domain_usergroup='[\"默认\"]'";
    
    if (
        $conn->query($ylb_usergroup) === TRUE && 
        $conn->query($ylb_usergroup_Data) === TRUE && 
        $conn->query($ALTER_user_group) === TRUE && 
        $conn->query($set_all_user_group) === TRUE && 
        $conn->query($ALTER_domain_usergroup) === TRUE && 
        $conn->query($set_all_domain_usergroup) === TRUE
        ) {
        
        // 读取文件内容
        $fileContents = file_get_contents('../../console/Db.php');
        
        // 新的版本号
        $newVersion = '2.2.0';
        
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