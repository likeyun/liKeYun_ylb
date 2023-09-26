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
    
    // 跳转卡表
    $ylb_jumpWeChat = "CREATE TABLE `ylb_jumpWeChat` (
      `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
      `jw_id` int(10) DEFAULT NULL COMMENT 'JWID',
      `jw_title` varchar(64) DEFAULT NULL COMMENT '标题',
      `jw_icon` text COMMENT '图标链接',
      `jw_bgimg` text COMMENT '背景图片',
      `jw_yccym` varchar(255) DEFAULT NULL COMMENT '云储存域名',
      `jw_url` text COMMENT '目标链接',
      `jw_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问次数',
      `jw_clickNum` int(10) DEFAULT '0' COMMENT '点击次数',
      `jw_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
      `jw_token` varchar(32) DEFAULT NULL COMMENT 'Token',
      `jw_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    if ($conn->query($ylb_jumpWeChat) === TRUE) {
            
        // 休息2秒
        sleep(2);
        
        // 读取文件内容
        $fileContents = file_get_contents('../../console/Db.php');
        
        // 新的版本号
        $newVersion = '2.1.0';
        
        // 使用正则表达式替换旧版本号
        $updatedContents = preg_replace("/'version' => '[^']*'/", "'version' => '$newVersion'", $fileContents);
        
        // 将更新后的内容写回文件
        file_put_contents('../../console/Db.php', $updatedContents);
        
        // 休息3秒
        sleep(3);
        
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