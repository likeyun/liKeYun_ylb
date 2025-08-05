<?php

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
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 2.4.6新增，新增一个长按次数字段
        $checkExitsSQL = "SHOW COLUMNS FROM huoma_qun_zima LIKE 'longpress_num'";
        $checkExits = $db->set_table('huoma_qun_zima')->findSql($checkExitsSQL);
        if(!$checkExits) {
            
            // 不存在这个字段
            // 新增字段
            $Add_longpress_num = "ALTER TABLE huoma_qun_zima ADD longpress_num int(10) DEFAULT '0' COMMENT '长按次数'";
            $db->set_table('huoma_qun_zima')->findSql($Add_longpress_num);
        }
    
    	// 数据库huoma_qun表
    	$huoma_qun = $db->set_table('huoma_qun');
    
    	// 获取总数
    	$qunNum = $huoma_qun->getCount(['qun_creat_user'=>$LoginUser]);
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($qunNum/$lenght);
    
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
    
    	// 执行查询（查询当前登录用户创建的群活码，每页10个DESC排序）
    	$find = $huoma_qun->findAll(
    	    $conditions = ['qun_creat_user' => $LoginUser],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 有结果
    	if($find && $find > 0){
    	    
    		$result = array(
    		    'qunList' => $find,
    		    'qunNum' => $qunNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 无结果
            $result = array(
                'code' => 204,
                'msg' => '暂无群活码'
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