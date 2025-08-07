<?php

	// 编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
    	@$page = $_GET['p']?$_GET['p']:1;
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../../../../Db.php';
        
        // 实例化类
    	$db = new DB_API($config);
    	
        // 2.3.0版本检查（自动升级）
        // 检查表 ylb_jumpWX 是否存在 jw_expire_time 这个字段
        $mysqli_Db = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        $check_jw_expire = $mysqli_Db->query("SHOW COLUMNS FROM `ylb_jumpWX` LIKE 'jw_expire_time'");
        
        // 如果不存在
        if ($check_jw_expire && $check_jw_expire->num_rows == 0) {
        
            // 添加 jw_expire_time 这个字段
            $addColumn_jw_expire_time = "ALTER TABLE `ylb_jumpWX` 
                     ADD COLUMN `jw_expire_time` VARCHAR(32) DEFAULT '2035-12-31 23:59:59' COMMENT '到期时间' 
                     AFTER `jw_create_time`";
                     
            // 添加 jw_key 这个字段
            $addColumn_jw_key = "ALTER TABLE `ylb_jumpWX` 
                     ADD COLUMN `jw_key` TEXT DEFAULT NULL COMMENT '随机参数Key' 
                     AFTER `jw_token`";
                     
            // 添加 jw_beizhu_text 这个字段
            $addColumn_jw_beizhu_msg = "ALTER TABLE `ylb_jumpWX` 
                     ADD COLUMN `jw_beizhu_msg` VARCHAR(64) DEFAULT NULL COMMENT '后台的备注信息' 
                     AFTER `jw_beizhu`";
            
            // 执行
            $mysqli_Db->query($addColumn_jw_expire_time);
            $mysqli_Db->query($addColumn_jw_key);
            $mysqli_Db->query($addColumn_jw_beizhu_msg);
        }
        
        // 2.4.0版本检查（自动升级）
        // 检查表 ylb_jumpWX 是否存在 jw_douyin_landpage 这个字段
        $mysqli_Db = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        $check_jw_douyin_landpage = $mysqli_Db->query("SHOW COLUMNS FROM `ylb_jumpWX` LIKE 'jw_douyin_landpage'");
        
        // 如果不存在
        if ($check_jw_douyin_landpage && $check_jw_douyin_landpage->num_rows == 0) {
        
            // 添加 jw_douyin_landpage 这个字段
            $addColumn_jw_douyin_landpage = "ALTER TABLE `ylb_jumpWX` 
                     ADD COLUMN `jw_douyin_landpage` TEXT DEFAULT NULL COMMENT '通用落地页'";
                     
            // 添加 jw_common_landpage 这个字段
            $addColumn_jw_common_landpage = "ALTER TABLE `ylb_jumpWX` 
                     ADD COLUMN `jw_common_landpage` TEXT DEFAULT NULL COMMENT '抖音专用落地页'";
            
            // 执行
            $mysqli_Db->query($addColumn_jw_douyin_landpage);
            $mysqli_Db->query($addColumn_jw_common_landpage);
        }
        
        // 2.4.1版本检查（自动升级）
        // 检查表 ylb_jumpWX 是否存在 jw_fwl_limit 这个字段
        $check_jw_fwl_limit = $mysqli_Db->query("SHOW COLUMNS FROM `ylb_jumpWX` LIKE 'jw_fwl_limit'");
        
        // 如果不存在
        if ($check_jw_fwl_limit && $check_jw_fwl_limit->num_rows == 0) {
        
            // 添加 jw_fwl_limit 这个字段
            $addColumn_jw_fwl_limit = "ALTER TABLE `ylb_jumpWX` 
                     ADD COLUMN `jw_fwl_limit` INT(10) DEFAULT '100000' COMMENT '访问量限制'";
                     
            // 添加 jw_status 这个字段
            $addColumn_jw_status = "ALTER TABLE `ylb_jumpWX` 
                     ADD COLUMN `jw_status` INT(1) DEFAULT '1' COMMENT '状态 1开 2关'";
            // 执行
            $mysqli_Db->query($addColumn_jw_fwl_limit);
            $mysqli_Db->query($addColumn_jw_status);
            
            // 更新配置文件
            require_once 'updateAppJSON.php';
        }
        
        // 验证超管账号
        $checkUser  = $db->set_table('huoma_user')->find(['user_name'=>$LoginUser]);
        $user_admin = $checkUser['user_admin'];
        
        if((int)$user_admin == 1) {
            
            // 超管
            // 统计所有的数据总数
            $jwNum = $db->set_table('ylb_jumpWX')->getCount(['1'=>'1']);
        }else {
            
            // 非超管
            // 统计当前登录的数据总数
            $jwNum = $db->set_table('ylb_jumpWX')->getCount(['jw_create_user'=>$LoginUser]);
        }
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($jwNum/$lenght);
    
    	// 上一页     
    	$prepage = $page-1;
    	if($page == 1){
    		$prepage=1;
    	}
    
    	// 下一页
    	$nextpage = $page+1;
    	if($page == $allpage){
    		$nextpage=$allpage;
    	}
    
    	if((int)$user_admin == 1) {
    	    
    	   // 超管
    	   // 获取所有用户的数据
    	   $getJwList = $db->set_table('ylb_jumpWX')->findAll(
        	    $conditions = ['1'=>'1'],
        	    $order = 'ID DESC',
        	    $fields = null,
        	    $limit = ''.$offset.','.$lenght.''
        	);
    	}else {
    	    
    	   // 非超管
    	   // 获取当前登录的用户的数据
    	   $getJwList = $db->set_table('ylb_jumpWX')->findAll(
        	    $conditions = ['jw_create_user' => $LoginUser],
        	    $order = 'ID DESC',
        	    $fields = null,
        	    $limit = ''.$offset.','.$lenght.''
        	);
    	}
    	
        // 判断获取结果
    	if($getJwList){
    	    
    	    // 获取成功
    		$result = array(
    		    'jwList' => $getJwList,
    		    'jwNum' => $jwNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功',
    		    'user_admin' => $user_admin,
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无链接'
            );
    	}
    
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>