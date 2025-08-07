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
    
    // 所有的页面
    $allNavList = [
        [
            "href" => "../index/",
            "icon" => "i-data",
            "text" => "数据"
        ],
        [
            "href" => "../qun/",
            "icon" => "i-hm",
            "text" => "活码"
        ],
        [
            "href" => "../dwz/",
            "icon" => "i-dwz",
            "text" => "短网址"
        ],
        [
            "href" => "../tbk/",
            "icon" => "i-tbk",
            "text" => "淘宝客"
        ],
        [
            "href" => "../shareCard/",
            "icon" => "i-share",
            "text" => "分享卡片"
        ],
        [
            "href" => "../plugin/",
            "icon" => "i-plugin",
            "text" => "插件中心"
        ],
        [
            "href" => "../kami/",
            "icon" => "i-kami",
            "text" => "卡密分发"
        ],
        [
            "href" => "../config/",
            "icon" => "i-config",
            "text" => "配置中心"
        ],
        [
            "href" => "../sucai/",
            "icon" => "i-sucai",
            "text" => "素材管理"
        ],
        [
            "href" => "../user/",
            "icon" => "i-account",
            "text" => "账号管理"
        ]
    ];
    
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
    
    // 用户组
    $usergroup = trim($_GET['usergroup']);
    
    if(!$usergroup) {
        $result = array(
    		'code' => 201,
            'msg' => '请传入用户组名称'
    	);
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if($usergroup) {
        
        // 查询当前用户组的页面授权情况
        $getNavList = $db->set_table('ylb_usergroup')->find(['usergroup_name' => $usergroup]);
        
        if($getNavList) {
            
            // 获取成功
            // 当前用户组授权的页面
            $navList = $getNavList['navList'];
            
            // 返回JSON
            $result = array(
        		'code' => 200,
                'msg' => '获取成功',
                'currentUser_usergroup' => $usergroup,
                'allNavList' => $allNavList,
                'navList' => json_decode($navList)
        	);
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }else {
            
            // 获取当前用户组的页面授权情况失败
            $result = array(
        		'code' => 202,
                'msg' => '无法获取到当前用户组'
        	);
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
?>