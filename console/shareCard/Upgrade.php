<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    	
    	// 连接数据库
        $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

        // 验证是否存在huoma_shareCard表
        $conn->query('SELECT * FROM huoma_shareCard');
        if(preg_match("/huoma_shareCard' doesn/", $conn->error)){
            
            // 不存在huoma_shareCard表
            // 创建huoma_shareCard表
            $huoma_shareCard = "CREATE TABLE `huoma_shareCard` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `shareCard_id` int(10) DEFAULT NULL COMMENT '卡片ID',
              `shareCard_title` varchar(64) DEFAULT NULL COMMENT '标题',
              `shareCard_desc` text COMMENT '摘要',
              `shareCard_img` text COMMENT '分享图',
              `shareCard_rkym` text COMMENT '入口域名',
              `shareCard_ldym` text COMMENT '落地域名',
              `shareCard_url` text COMMENT '目标链接',
              `shareCard_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `shareCard_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `shareCard_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `shareCard_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            if($conn->query($huoma_shareCard) === TRUE){
                
                // 创建成功
                $_SESSION["huoma_shareCard"] = 'SUCCESS';
            }
        }
        
        // 验证是否存在huoma_shareCardConfig表
        $conn->query('SELECT * FROM huoma_shareCardConfig');
        if(preg_match("/huoma_shareCardConfig' doesn/", $conn->error)){
            
            // 不存在huoma_shareCardConfig表
            // 创建huoma_shareCardConfig表
            $huoma_shareCardConfig = "CREATE TABLE `huoma_shareCardConfig` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `appid` varchar(32) DEFAULT NULL COMMENT '公众号appid',
              `appsecret` varchar(64) DEFAULT NULL COMMENT '公众号appsecret',
              `access_token` text COMMENT 'access_token',
              `access_token_expire_time` varchar(32) DEFAULT NULL COMMENT 'access_token_expire_time',
              `jsapi_ticket` text COMMENT 'jsapi_ticket',
              `jsapi_ticket_expire_time` varchar(32) DEFAULT NULL COMMENT 'jsapi_ticket_expire_time'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            if($conn->query($huoma_shareCardConfig) === TRUE){
                
                // 创建成功
                $_SESSION["huoma_shareCardConfig"] = 'SUCCESS';
            }
        }
        
        // 默认数据
        $huoma_shareCardConfigData = "INSERT INTO `huoma_shareCardConfig` (`appid`, `appsecret`) VALUES ('未设置', '未设置')";
        $conn->query($huoma_shareCardConfigData);
        
        // 验证创建或更新结果
        if(
            isset($_SESSION["huoma_shareCard"]) && 
            isset($_SESSION["huoma_shareCardConfig"])
        ){
            
            // 验证通过
            $result = array(
                'code' => 200,
                'msg' => '更新成功！'
            );
            
            // 删除SESSION
            delSession();
            
        }else{
            
            // 验证不通过
            $result = array(
                'code' => 202,
                'msg' => '更新失败，请检查数据库是否已存在huoma_shareCard、huoma_shareCardConfig这两个表，如果有请删除后重试！'
            );
            
            delSession();
        }
        
    }else{
        
        $result = array(
            'code' => 201,
            'msg' => '未登录或登录过期'
        );
    }
    
    // 删除SESSION
    function delSession(){
        unset($_SESSION["huoma_shareCard"]);
        unset($_SESSION["huoma_shareCardConfig"]);
    }

    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>