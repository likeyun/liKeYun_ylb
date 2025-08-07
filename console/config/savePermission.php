<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        // 已登录
        $usergroup = trim($_GET['usergroup']);
        $SelectedNavList = trim($_GET['SelectedNavList']);
    
        // 过滤参数
        if(empty($usergroup) || !isset($usergroup)){
    
            $result = array(
                'code' => 203,
                'msg' => '缺少用户组'
            );
        }else if(empty($SelectedNavList) || !isset($SelectedNavList)){
    
            $result = array(
                'code' => 203,
                'msg' => '请选择要授权的页面'
            );
        }else if(count(json_decode($SelectedNavList)) == 0){
    
            $result = array(
                'code' => 203,
                'msg' => '至少要选择一项进行授权'
            );
        }else{
    
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
    
            // 数据库配置
            include '../Db.php';
    
            // 实例化类
            $db = new DB_API($config);
    
            // 获取当前登录用户的管理权限
            $user_admin = json_decode(json_encode($db->set_table('huoma_user')->find(['user_name' => $LoginUser])))->user_admin;
            if($user_admin == 2){
    
                // 没有管理权限
                $result = array(
                    'code' => 202,
                    'msg' => '没有管理权限'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
    
            // 提交更新
            $update = $db->set_table('ylb_usergroup')->update(
                ['usergroup_name' => $usergroup],
                ['navList' => $SelectedNavList]
            );
    
            if($update){
    
                // 已设置
                $result = array(
                    'code' => 200,
                    'msg' => '已保存'
                );
            }else{
    
                // 设置失败
                $result = array(
                    'code' => 202,
                    'msg' => '保存失败'
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