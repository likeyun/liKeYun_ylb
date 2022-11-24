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

        // 验证是否存在huoma_dwz表
        $conn->query('SELECT * FROM huoma_dwz');
        if(preg_match("/huoma_dwz' doesn/", $conn->error)){
            
            // 不存在huomadwzk表
            // 创建huoma_dwz表
            $huoma_dwz = "CREATE TABLE `huoma_dwz` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `dwz_id` int(10) DEFAULT NULL COMMENT '短网址ID',
              `dwz_title` varchar(32) DEFAULT NULL COMMENT '标题',
              `dwz_key` varchar(10) DEFAULT NULL COMMENT '短网址Key',
              `dwz_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `dwz_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
              `dwz_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `dwz_url` text DEFAULT NULL COMMENT '目标链接',
              `dwz_type` int(2) DEFAULT NULL COMMENT '访问限制',
              `dwz_rkym` text COMMENT '入口域名',
              `dwz_zzym` text COMMENT '中转域名',
              `dwz_dlym` text COMMENT '短链域名',
              `dwz_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            if($conn->query($huoma_dwz) === TRUE){
                
                // 创建成功
                $_SESSION["huoma_dwz"] = 'SUCCESS';
            }
        }
        
        // 验证是否存在huoma_dwz_apikey表
        $conn->query('SELECT * FROM huoma_dwz_apikey');
        if(preg_match("/huoma_dwz_apikey' doesn/", $conn->error)){
            
            // 不存在huoma_dwz_apikey表
            // 创建huoma_dwz_apikey表
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            if($conn->query($huoma_dwz_apikey) === TRUE){
                
                // 创建成功
                $_SESSION["huoma_dwz_apikey"] = 'SUCCESS';
            }
        }
        
        // 验证huoma_count表里面的count_dwz_pv字段是否存在
        $conn->query('SELECT count_dwz_pv FROM huoma_count');
        if(preg_match("/Unknown column 'count_dwz_pv'/", $conn->error)){
            
            // 不存在count_dwz_pv字段
            // 创建count_dwz_pv字段至huoma_count表
            $add_count_dwz_pv = "ALTER TABLE `huoma_count` ADD `count_dwz_pv` int(10) NOT NULL DEFAULT '0' COMMENT '短网址'";
            
            if($conn->query($add_count_dwz_pv) === TRUE){
                
                // 创建成功
                $_SESSION["add_count_dwz_pv"] = 'SUCCESS';
            }
        }
        
        // 验证创建或更新结果
        if(
            isset($_SESSION["huoma_dwz"]) && 
            isset($_SESSION["huoma_dwz_apikey"]) && 
            isset($_SESSION["add_count_dwz_pv"])
        ){
            
            // 验证通过
            $result = array(
                'code' => 200,
                'msg' => '更新成功！'
            );
            
            delSession();
            
        }else{
            
            // 验证不通过
            $result = array(
                'code' => 202,
                'msg' => '更新失败，请检查数据库是否已存在huoma_dwz、huoma_dwz_apikey这两个表，以及huoma_count表里面是否存在count_dwz_pv这个字段。如果有请删除后重试！'
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
        
        unset($_SESSION["huoma_dwz"]);
        unset($_SESSION["huoma_dwz_apikey"]);
        unset($_SESSION["add_count_dwz_pv"]);
    }

    echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>