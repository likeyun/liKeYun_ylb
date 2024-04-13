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
    	@$page = $_GET['p']?$_GET['p']:1;
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
        
        // 实例化类
    	$db = new DB_API($config);
    	
    	// 获取所有的提取记录总数
    	$allNum = $db->set_table('ylb_km_openid')->getCount(['status' => 1]);
    
    	// 每页数量
    	$lenght = 12;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($allNum/$lenght);
    
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
    
    	// 获取所有的提取记录
    	$getExtractRecords = $db->set_table('ylb_km_openid')->findAll(
    	    $conditions = ['status' => 1],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
    	// 获取当前登录用户的管理权限
        $checkUser = $db->set_table('huoma_user')->find(['user_name' => $LoginUser]);
        $user_admin = $checkUser['user_admin'];
    	
        // 获取结果
    	if($getExtractRecords){
    	    
    	    // 获取成功
    		$result = array(
    		    'extractRecords' => $getExtractRecords,
    		    'allNum' => $allNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功',
    		    'user_admin' => $user_admin
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无提取记录',
                'user_admin' => $user_admin
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