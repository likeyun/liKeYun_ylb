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
	
	// 数据库配置
	include '../../Db.php';
    
    // 实例化类
	$db = new DB_API($config);
	
    // 接收参数
    $openid = $_GET['openid'];
    
    if($openid) {
        
        // 获取提取列表
    	$getTiquList = $db->set_table('ylb_km_openid')->findAll(
    	    $conditions = ['openid' => $openid],
    	    $order = 'ID DESC',
    	    $fields = '*',
    	    $limit = null
    	);
    	
        // 获取结果
    	if($getTiquList){
    	    
    	    // 获取成功
    		$result = array(
    		    'tiquList' => $getTiquList,
    		    'code' => 200,
    		    'msg' => '获取成功',
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无提取记录',
            );
    	}
    }else {
        
        // 获取失败
        $result = array(
            'code' => 205,
            'msg' => '参数缺失',
        );
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>