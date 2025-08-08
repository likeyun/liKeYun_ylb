<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 数据库配置
	include '../Db.php';

	// 实例化类
	$db = new DB_API($config);
	
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
    	// 已登录
        // 获取用户管理权限
        $checkUser = $db->set_table('huoma_user')->find(['user_name' => $_SESSION["yinliubao"]]);
        $user_admin = $checkUser['user_admin'];
        $user_name = $checkUser['user_name'];
        
        // 自动更新程序
        // -------------------------------------------------
        
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
        
        // -------------------------------------------------
        // 已登录
        $result = array(
            'code' => 200,
            'msg' => '已登录',
            'user_name' => $user_name,
            'user_admin' => $user_admin,
            'version' => '当前版本：' . $config['version']
        );
    }else{
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录',
            'version' => '当前版本：' . $config['version']
        );
    }

    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>