<?php

// 页面编码
header("Content-type:application/json");

// 判断登录状态
session_start();
if(isset($_SESSION["yinliubao"])){

    // 已登录
    $beizhu = trim($_GET['beizhu']);
    $domain_id = trim($_GET['domain_id']);

    // 过滤参数
    if(empty($beizhu) || !isset($beizhu)){

        $result = array(
            'code' => 203,
            'msg' => '请输入备注信息'
        );
    }else if(empty($domain_id) || !isset($domain_id)){

        $result = array(
            'code' => 203,
            'msg' => '缺少参数domain_id'
        );
    }else{

        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];

        // 数据库配置
        include '../Db.php';

        // 实例化类
        $db = new DB_API($config);

        // 获取当前登录用户的管理权限
        $user_admin = json_decode(json_encode($db->set_table('huoma_user')->find(['user_name'=>$LoginUser])))->user_admin;
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
        $updateBz = $db->set_table('huoma_domain')->update(
            ['domain_id' => $domain_id],
            ['domain_beizhu' => $beizhu]
        );

        if($updateBz){

            // 已设置
            $result = array(
                'code' => 200,
                'msg' => '已设置'
            );
        }else{

            // 设置失败
            $result = array(
                'code' => 202,
                'msg' => '设置失败'
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