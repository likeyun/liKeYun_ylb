<?php

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
    	
        // 检查是否存在【user_expire_time】这个字段
        // 该检查是为了自动完成更新
        // 2024年12月22日加入
        $field = 'user_expire_time'; // 字段名
        $tableName = 'huoma_user'; // 表名
        $checkExitsSQL = "SHOW COLUMNS FROM `$tableName` LIKE '$field'";
        $checkExits = $db->set_table($tableName)->findSql($checkExitsSQL);
        if(!$checkExits) {
            $Add = "ALTER TABLE huoma_user ADD user_expire_time timestamp DEFAULT '2035-12-31 23:59:59' COMMENT '到期时间' AFTER user_creat_time";
            $db->set_table($tableName)->findSql($Add);
        }
        
        // 检查是否存在【navList】这个字段
        // 该检查是为了自动完成更新
        // 2025年8月7日加入
        $field = 'navList'; // 字段名
        $tableName = 'ylb_usergroup'; // 表名
        $checkExitsSQL = "SHOW COLUMNS FROM `$tableName` LIKE '$field'";
        $checkExits = $db->set_table($tableName)->findSql($checkExitsSQL);
        if(!$checkExits) {
            
            // 先新增字段
            $Add = "ALTER TABLE $tableName ADD $field TEXT DEFAULT NULL COMMENT '授权页面'";
            $db->set_table($tableName)->findSql($Add);
            
            // 再设置默认数据
            $navListData = '[{"href":"../index/","icon":"i-data","text":"数据"},{"href":"../qun/","icon":"i-hm","text":"活码"},{"href":"../dwz/","icon":"i-dwz","text":"短网址"},{"href":"../tbk/","icon":"i-tbk","text":"淘宝客"},{"href":"../shareCard/","icon":"i-share","text":"分享卡片"},{"href":"../plugin/","icon":"i-plugin","text":"插件中心"},{"href":"../kami/","icon":"i-kami","text":"卡密分发"},{"href":"../config/","icon":"i-config","text":"配置中心"},{"href":"../sucai/","icon":"i-sucai","text":"素材管理"},{"href":"../user/","icon":"i-account","text":"账号管理"}]';
            $update = "UPDATE ylb_usergroup SET navList = '$navListData'";
            $db->set_table($tableName)->findSql($update);
        }
    	
        // 获取当前登录用户的账号权限
        $getCurrentAdmin = $db->set_table('huoma_user')->find(['user_name' => $LoginUser]);
        $user_admin = $getCurrentAdmin['user_admin'];
        
        // 根据权限选择返回用户列表
        if($user_admin == 1){
            
            // 管理员
            // 获取所有用户列表
            $getAllUser = $db->set_table('huoma_user')->findAll();
            
            // 所有用户的总数（对返回的用户列表进行计算数组对象个数）
            $userNum = count($getAllUser);
        }else{
            
            // 非管理员
            // 获取当前用户列表
            $getThisUser = $db->set_table('huoma_user')->find(['user_name'=>$LoginUser]);
            
            // 当前用户的总数（只有1个用户）
            $userNum = 1;
        }

    	// 每页数量
    	$lenght = 12;
    
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
        
        // 获取用户列表
        $getuserList = $db->set_table('huoma_user')->findAll(
    	    $conditions = $condition,
    	    $order = 'ID ASC',
    	    $fields = 'user_id,user_name,user_email,user_creat_time,user_expire_time,user_admin,user_status,user_manager,user_beizhu,user_group',
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