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
        // 接收参数
    	$dwz_key = trim($_POST['dwz_key']);
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 过滤参数
    	if(empty($dwz_key) || !isset($dwz_key)){
    	    
    	    $result = array(
    		    'code' => 203,
    		    'msg' => '请输入你要查询的短网址Key'
    		);
    	}else{
    	    
    	    // 数据库配置
        	include '../Db.php';
            
            // 实例化类
        	$db = new DB_API($config);
        
        	// 获取当前登录用户创建的短网址
        	$getdwzList = $db->set_table('huoma_dwz')->find([
        	    'dwz_creat_user' => $LoginUser,
        	    'dwz_key' => $dwz_key
        	]);
        	
            // 判断获取结果
        	if($getdwzList && $getdwzList > 0){
        	    
        	    // 获取成功
        		$result = array(
        		    'dwzList' => $getdwzList,
        		    'code' => 200,
        		    'msg' => '获取成功'
        		);
        	}else{
        	    
        	    // 获取失败
                $result = array(
                    'code' => 204,
                    'msg' => '无法查询到相关短网址'
                );
        	}
    	}

    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录或登录过期'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>