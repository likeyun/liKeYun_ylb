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
    $jw_id = trim(intval($_GET['jwid']));
    
    // 过滤参数
    if(empty($jw_id) || !isset($jw_id)){
        
        $result = array(
            'code' => 203,
            'msg' => '缺少必要参数'
        );
    }else{
        
        // 数据库配置
        include '../../../../Db.php';
    
        // 实例化类
        $db = new DB_API($config);
        
        // 获取clickNum
        $clickNum = json_decode(json_encode($db->set_table('ylb_jumpWX')->find(['jw_id'=>$jw_id])))->jw_clickNum;
        
        // 新的clickNum
        $jw_clickNum = $clickNum + 1;
        
        // 执行更新
        $updateClickNum = $db->set_table('ylb_jumpWX')->update(['jw_id'=>$jw_id],['jw_clickNum'=>$jw_clickNum]);
        if($updateClickNum){
            
            // 更新成功
            $result = array(
                'code' => 200,
                'msg' => $jw_id . '点击次数更新成功'
            );
        }else{
            
            // 更新失败
            $result = array(
                'code' => 202,
                'msg' => $jw_id . '更新失败'
            );
        }
    }

    // 输出callback
    $resultCallback = json_encode($result);
    echo $_GET['callback'] . "(" . $resultCallback . ")";
    
?>