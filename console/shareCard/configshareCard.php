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
        $appid = trim($_POST['appid']);
        $appsecret = trim($_POST['appsecret']);
        
        // 过滤参数
        if(empty($appid) || !isset($appid) || $appid == '未设置'){
            
            $result = array(
                'code' => 203,
                'msg' => 'appid未填写'
            );
        }else if(empty($appsecret) || !isset($appsecret) || $appsecret == '未设置'){
            
            $result = array(
                'code' => 203,
                'msg' => 'appsecret未填写'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 需更新的字段
            $updateshareCardConfigData = [
                'appid'=>$appid,
                'appsecret'=>$appsecret
            ];
            
            // 更新条件
            $updateshareCardConfigDataCondition = [
                'id' => 1
            ];
            
            // 执行更新update(条件，字段)
            $updateshareCardConfigResult = $db->set_table('huoma_shareCardConfig')->update($updateshareCardConfigDataCondition,$updateshareCardConfigData);
            if($updateshareCardConfigResult){
                
                // 更新成功
                $result = array(
                    'code' => 200,
                    'msg' => '配置成功'
                );
            }else{
                
                // 更新失败
                $result = array(
                    'code' => 202,
                    'msg' => '配置失败'
                );
            }
            
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