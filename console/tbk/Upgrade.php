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

        // 验证是否存在huoma_tbk表
        $conn->query('SELECT * FROM huoma_tbk');
        if(preg_match("/huoma_tbk' doesn/", $conn->error)){
            
            // 不存在huoma_tbk表
            // 创建huoma_tbk表
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            if($conn->query($huoma_tbk) === TRUE){
                
                // 创建成功
                $_SESSION["huoma_tbk"] = 'SUCCESS';
            }
        }
        
        // 验证是否存在huoma_tbk_config表
        $conn->query('SELECT * FROM huoma_tbk_config');
        if(preg_match("/huoma_tbk_config' doesn/", $conn->error)){
            
            // 不存在huoma_tbk_config表
            // 创建huoma_tbk_config表
            $huoma_tbk_config = "CREATE TABLE `huoma_tbk_config` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `zjy_config_appkey` varchar(64) DEFAULT NULL COMMENT '折淘客appkey',
              `zjy_config_sid` varchar(32) DEFAULT NULL COMMENT '折淘客sid',
              `zjy_config_pid` varchar(64) DEFAULT NULL COMMENT '你的pid',
              `zjy_config_tbname` varchar(32) DEFAULT NULL COMMENT '淘宝账号',
              `zjy_config_user` varchar(32) DEFAULT NULL COMMENT '你的引流宝账号'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            if($conn->query($huoma_tbk_config) === TRUE){
                
                // 创建成功
                $_SESSION["huoma_tbk_config"] = 'SUCCESS';
            }
        }
        
        // 验证huoma_count表里面的count_zjy_pv字段是否存在
        $conn->query('SELECT count_zjy_pv FROM huoma_count');
        if(preg_match("/Unknown column 'count_zjy_pv'/", $conn->error)){
            
            // 不存在count_zjy_pv字段
            // 创建count_zjy_pv字段至huoma_count表
            $add_count_zjy_pv = "ALTER TABLE `huoma_count` ADD `count_zjy_pv` int(10) NOT NULL DEFAULT '0' COMMENT '淘宝客'";
            
            if($conn->query($add_count_zjy_pv) === TRUE){
                
                // 创建成功
                $_SESSION["add_count_zjy_pv"] = 'SUCCESS';
            }
        }
        
        // 淘宝客接口配置默认数据
        $huoma_tbk_config_Data = "INSERT INTO `huoma_tbk_config` (`zjy_config_appkey`, `zjy_config_sid`, `zjy_config_pid`, `zjy_config_tbname`, `zjy_config_user`) VALUES ('未设置', '未设置', '未设置', '未设置', '$LoginUser')";
        $conn->query($huoma_tbk_config_Data);
        
        // 验证创建或更新结果
        if(
            isset($_SESSION["huoma_tbk"]) && 
            isset($_SESSION["huoma_tbk_config"]) && 
            isset($_SESSION["add_count_zjy_pv"])
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
                'msg' => '更新失败，请检查数据库是否已存在huoma_tbk、huoma_tbk_config这两个表，以及huoma_count表里面是否存在count_zjy_pv这个字段。如果有请删除后重试！'
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
        unset($_SESSION["huoma_tbk"]);
        unset($_SESSION["huoma_tbk_config"]);
        unset($_SESSION["add_count_zjy_pv"]);
    }

    echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>