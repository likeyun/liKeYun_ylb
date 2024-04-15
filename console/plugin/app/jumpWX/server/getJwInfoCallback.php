<?php
    
    // 获取jwid
	$jw_id = trim(intval($_GET['jwid']));
        
    // 过滤参数
    if(empty($jw_id) || !isset($jw_id)){
        
        // 缺少必要参数
        $result = array(
            "code" => 201,
            "msg" => "缺少必要参数"
        );
    }else{
        
        // 数据库配置
    	include '../../../../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    
    	// 获取当前jw_id的详情
        $getJwInfo = $db->set_table('ylb_jumpWX')->find(['jw_id'=>$jw_id]);
        
        // 更新访问次数
        $jw_pv = json_decode(json_encode($getJwInfo))->jw_pv;
        $newPV = $jw_pv + 1;
        $db->set_table('ylb_jumpWX')->update(['jw_id'=>$jw_id],['jw_pv'=>$newPV]);

        // 返回数据
        if($getJwInfo){
            
            // 有结果
            $result = array(
                "code" => 200,
                "msg" => "获取成功",
                "jwInfo" => $getJwInfo
            );
        }else{
            
            // 获取失败
            $result = array(
                "code" => 202,
                "msg" => "获取失败"
            );
        }
    }

    // 输出callback
    $resultCallback = json_encode($result);
    echo $_GET['callback'] . "(" . $resultCallback . ")";
    
?>