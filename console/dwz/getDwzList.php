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
    	@$page = $_GET['p']?$_GET['p']:1;
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    	
        // 面向对象连接数据库
        $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        
        // 验证是否存在huoma_dwz表
        $conn->query('SELECT * FROM huoma_dwz');
        if(preg_match("/huoma_dwz' doesn/", $conn->error)){
            
            // 不存在huoma_dwz表
            $result = array(
    			'code' => 205,
                'msg' => '点击这里进行升级'
    		);
    		echo json_encode($result,JSON_UNESCAPED_UNICODE);
    		exit;
        }
        
        // 验证是否存在huoma_dwz_apikey表
        $conn->query('SELECT * FROM huoma_dwz_apikey');
        if(preg_match("/huoma_dwz_apikey' doesn/", $conn->error)){
            
            // 不存在huoma_dwz_apikey表
            $result = array(
    			'code' => 205,
                'msg' => '点击这里进行升级'
    		);
    		echo json_encode($result,JSON_UNESCAPED_UNICODE);
    		exit;
        }
        
        // 验证huoma_count表里面的count_dwz_pv字段是否存在
        $conn->query('SELECT count_dwz_pv FROM huoma_count');
        if(preg_match("/Unknown column 'count_dwz_pv'/", $conn->error)){
            
            // 不存在count_dwz_pv字段
            $result = array(
    			'code' => 205,
                'msg' => '点击这里进行升级'
    		);
    		echo json_encode($result,JSON_UNESCAPED_UNICODE);
    		exit;
        }
        
        // 实例化类
    	$db = new DB_API($config);
    	
    	$result = $db->set_table('huoma_dwz')->getCount(['dwz_creat_user'=>$LoginUser]);

    	// 获取总数
    	$dwzNum = $db->set_table('huoma_dwz')->getCount(['dwz_creat_user'=>$LoginUser]);
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($dwzNum/$lenght);
    
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
    
    	// 获取当前登录用户创建的短网址，每页10个DESC排序
    	$getdwzList = $db->set_table('huoma_dwz')->findAll(
    	    $conditions = ['dwz_creat_user' => $LoginUser],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 判断获取结果
    	if($getdwzList && $getdwzList > 0){
    	    
    	    // 获取成功
    		$result = array(
    		    'dwzList' => $getdwzList,
    		    'dwzNum' => $dwzNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无短网址'
            );
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