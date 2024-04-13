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
    	
        // 创建huoma_dwz表
        $huoma_dwz = "CREATE TABLE `huoma_dwz` (
          `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
          `dwz_id` int(10) DEFAULT NULL COMMENT '短网址ID',
          `dwz_title` varchar(32) DEFAULT NULL COMMENT '标题',
          `dwz_key` varchar(10) DEFAULT NULL COMMENT '短网址Key',
          `dwz_creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
          `dwz_pv` int(10) NOT NULL DEFAULT '0' COMMENT '访问量',
          `dwz_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
          `dwz_url` text COMMENT '目标链接',
          `dwz_type` int(2) DEFAULT NULL COMMENT '访问限制',
          `dwz_rkym` text COMMENT '入口域名',
          `dwz_zzym` text COMMENT '中转域名',
          `dwz_dlym` text COMMENT '短链域名',
          `dwz_creat_user` varchar(32) DEFAULT NULL COMMENT '创建者'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        
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
        
        // 添加count_dwz_pv字段至huoma_count表
        $huoma_count_count_dwz_pv = "ALTER TABLE `huoma_count` ADD `count_dwz_pv` int(10) NOT NULL DEFAULT '0' COMMENT '短网址'";
        
        // $huoma_dwz创建成功
        if($conn->query($huoma_dwz) === TRUE){
            
            // $huoma_dwz_apikey创建成功
            if($conn->query($huoma_dwz_apikey) === TRUE){
            
                // $huoma_count_count_dwz_pv创建成功
                if($conn->query($huoma_count_count_dwz_pv) === TRUE){
                
                    $result = array(
                        'code' => 200,
                        'msg' => '初始化成功！'
                    );
                }else{

                    if(preg_match("/Duplicate column name/", $conn->error)){
                
                        $result = array(
                            'code' => 202,
                            'msg' => '初始化失败！请检查数据库是否存在huoma_dwz表、huoma_dwz_apikey表、以及huoma_count表里面是否存在count_dwz_pv字段。如果存在，请删除后再尝试！'
                        );
                    }else{
                        
                        $result = array(
                            'code' => 202,
                            'msg' => '初始化失败！huoma_dwz_apikey表创建异常，错误信息：'.$conn->error
                        );
                    }
                }
            }else{
                
                if(preg_match("/already exists/", $conn->error)){
                
                    $result = array(
                        'code' => 202,
                        'msg' => '初始化失败！请检查数据库是否存在huoma_dwz表、huoma_dwz_apikey表、以及huoma_count表里面是否存在count_dwz_pv字段。如果存在，请删除后再尝试！'
                    );
                }else{
                    
                    $result = array(
                        'code' => 202,
                        'msg' => '初始化失败！huoma_dwz_apikey表创建异常，错误信息：'.$conn->error
                    );
                }
            }
        }else{
            
            if(preg_match("/already exists/", $conn->error)){
                
                $result = array(
                    'code' => 202,
                    'msg' => '初始化失败！请检查数据库是否存在huoma_dwz表、huoma_dwz_apikey表、以及huoma_count表里面是否存在count_dwz_pv字段。如果存在，请删除后再尝试！'
                );
            }else{
                
                $result = array(
                    'code' => 202,
                    'msg' => '初始化失败！huoma_dwz表创建异常，错误信息：'.$conn->error
                );
            }
        }
        
    }else{
        
        $result = array(
            'code' => 201,
            'msg' => '未登录或登录过期'
        );
    }
    
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>