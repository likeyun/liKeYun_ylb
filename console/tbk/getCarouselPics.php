<?php

	// 编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // ID
        $Carousel_id = trim($_GET['Carousel_id']);
        
        // 数据库配置
    	include '../Db.php';
        
        // 实例化类
    	$db = new DB_API($config);
    
    	// 获取当前登录用户创建的
    	$getCarouselPics = $db->set_table('ylb_CarouselSPA_pics')->findAll(
    	    $conditions = ['add_user' => $LoginUser, 'Carousel_id' => $Carousel_id],
    	    $order = 'ID ASC',
    	    $fields = null,
    	    $limit = null
    	);
    	
        // 结果
    	if($getCarouselPics){
    	    
    	    // 成功
    		$result = array(
    		    'carouselPics' => $getCarouselPics,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无轮播图'
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