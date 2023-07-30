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
    
    // 接收参数
    $zjy_id = trim(intval($_GET['zjy_id']));
    
    // 过滤参数
    // 过滤参数
    if(empty($zjy_id) || !isset($zjy_id)){
        
        $result = array(
            'code' => 203,
            'msg' => '非法请求'
        );
    }else{
        
        // 数据库配置
        include '../Db.php';
    
        // 实例化类
        $db = new DB_API($config);
        
        // 获取zjy_copyNum
        $zjy_copyNum = json_decode(json_encode($db->set_table('huoma_tbk')->find(['zjy_id'=>$zjy_id])))->zjy_copyNum;
        
        // 需更新的字段
        $zjy_copyNum = $zjy_copyNum+1;
        $updateZjyData = [
            'zjy_copyNum'=>$zjy_copyNum,
        ];
        
        // 更新条件
        $updateZjyDataCondition = [
            'zjy_id' => $zjy_id
        ];
        
        // 执行更新update(条件，字段)
        $updateZjyResult = $db->set_table('huoma_tbk')->update($updateZjyDataCondition,$updateZjyData);
        if($updateZjyResult){
            
            // 更新成功
            $result = array(
                'code' => 200,
                'msg' => '更新成功'
            );
        }else{
            
            // 更新失败
            $result = array(
                'code' => 202,
                'msg' => '更新失败'
            );
        }
    }

    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>