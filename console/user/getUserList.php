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
    
    	// 数据库huoma_user表
    	$huoma_user = $db->set_table('huoma_user');
    	
        // 获取当前登录用户的账号权限
        $getuserAdmin = ['user_name'=>$LoginUser];
        $getuserAdminResult = $huoma_user->find($getuserAdmin);
        
        // 权限 1管理 2非管理
        $user_admin = json_decode(json_encode($getuserAdminResult))->user_admin;
        
        // 根据权限选择返回用户列表
        if($user_admin == 1){
            
            // 管理员
            // 获取所有用户列表
            $getAllUser = $huoma_user->findAll();
            
            // 所有用户的总数（对返回的用户列表进行计算数组对象个数）
            $userNum = count($getAllUser);
        }else{
            
            // 非管理员
            // 获取当前用户列表
            $getThisUser = $huoma_user->find(['user_name'=>$LoginUser]);
            
            // 当前用户的总数（只有1个用户）
            $userNum = 1;
        }

    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($userNum/$lenght);
    
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
    	
    	
    	// 根据权限选择返回用户列表
        if($user_admin == 1){
            
            // 管理员
            $condition = null;
        }else{
            
            // 非管理员
            $condition = ['user_name' => $LoginUser];
        }
        
        // 获取用户列表（根据管理员和非管理员的查询条件去获取列表）
        $getuserList = $huoma_user->findAll(
    	    $conditions = $condition,
    	    $order = 'ID ASC',
    	    $fields = 'user_id,user_name,user_email,user_creat_time,user_admin,user_status,user_manager,user_beizhu,user_group',
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 判断获取结果
    	if($getuserList && $getuserList > 0){
    	    
    	    // 获取成功
    		$result = array(
    		    'userList' => $getuserList,
    		    'userNum' => $userNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'user_admin'=>$user_admin,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无账号'
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