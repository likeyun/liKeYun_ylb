<?php

    // 页面编码
    header('Content-Type: application/json');
    
    // 判断登录状态
    session_start();
    if(!isset($_SESSION["yinliubao"])) {
        
        // 未登录
        $result = array(
    		'code' => 201,
            'msg' => '未登录'
    	);
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 数据库配置
    include '../Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 当前登录的用户
    $currentUser = $_SESSION["yinliubao"];
    
    // 超管验证
    $checkUser = $db->set_table('huoma_user')->find(['user_name' => $currentUser]);
    $user_admin = $checkUser['user_admin'];
    if((int)$user_admin !== 1) {
        $result = array(
    		'code' => 201,
            'msg' => '无操作权限'
    	);
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 查询当前用户组的页面授权情况
    $getNavList = $db->set_table('ylb_usergroup')->findAll();
    
    if($getNavList) {
        
        // 返回JSON
        $result = array(
    		'code' => 200,
            'msg' => '获取成功',
            'getNavList' => $getNavList
    	);
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }else {
        
        // 失败
        $result = array(
    		'code' => 202,
            'msg' => '无法获取'
    	);
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
?>