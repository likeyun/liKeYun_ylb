<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	$keywordOrKey = trim($_GET['keywordOrKey']);
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 过滤参数
    	if(!isset($keywordOrKey) || empty($keywordOrKey)){
    	    
    	    $result = array(
    		    'code' => 203,
    		    'msg' => '请输入你要查询的关键词'
    		);
    	}else{
    	    
    	    // 数据库配置
        	include '../Db.php';
            
            // 实例化类
        	$db = new DB_API($config);
        
        	// 获取当前登录用户创建的
            // 根据标题或Key查询
            $searchSQL = "SELECT * FROM ylb_CarouselSPA WHERE Carousel_title LIKE '%$keywordOrKey%' OR Carousel_key LIKE '%$keywordOrKey%' AND Carousel_create_user = '$LoginUser'";
            $searchRET = $db->set_table('ylb_CarouselSPA')->findSql($searchSQL);
        	
        	if($searchRET){
        	    
        	    // 成功
        		$result = array(
        		    'carouselSPAList' => $searchRET,
        		    'code' => 200,
        		    'msg' => '查询成功'
        		);
        	}else{
        	    
        	    // 获取失败
                $result = array(
                    'code' => 204,
                    'msg' => '无法查询到相关内容'
                );
        	}
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