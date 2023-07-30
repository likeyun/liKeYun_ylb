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
        $zjy_config_appkey = trim($_POST['zjy_config_appkey']);
        $zjy_config_sid = trim($_POST['zjy_config_sid']);
        $zjy_config_pid = trim($_POST['zjy_config_pid']);
        $zjy_config_tbname = trim($_POST['zjy_config_tbname']);
        
        // 过滤参数
        if(empty($zjy_config_appkey) || !isset($zjy_config_appkey) || $zjy_config_appkey == '未设置'){
            
            $result = array(
                'code' => 203,
                'msg' => 'appkey未填写'
            );
        }else if(empty($zjy_config_sid) || !isset($zjy_config_sid) || $zjy_config_sid == '未设置'){
            
            $result = array(
                'code' => 203,
                'msg' => 'sid未填写'
            );
        }else if(empty($zjy_config_pid) || !isset($zjy_config_pid) || $zjy_config_pid == '未设置'){
            
            $result = array(
                'code' => 203,
                'msg' => 'pid未填写'
            );
        }else if(empty($zjy_config_tbname) || !isset($zjy_config_tbname) || $zjy_config_tbname == '未设置'){
            
            $result = array(
                'code' => 203,
                'msg' => '淘宝账号未填写'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 需更新的字段
            $updateZjyConfigData = [
                'zjy_config_appkey'=>$zjy_config_appkey,
                'zjy_config_sid'=>$zjy_config_sid,
                'zjy_config_pid'=>$zjy_config_pid,
                'zjy_config_tbname'=>$zjy_config_tbname
            ];
            
            // 更新条件
            $updateZjyConfigDataCondition = [
                'zjy_config_user' => $LoginUser
            ];
            
            // 执行更新update(条件，字段)
            $updateZjyConfigResult = $db->set_table('huoma_tbk_config')->update($updateZjyConfigDataCondition,$updateZjyConfigData);
            if($updateZjyConfigResult){
                
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