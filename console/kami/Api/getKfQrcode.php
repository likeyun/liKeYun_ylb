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
    
    // 获取客服二维码
	$getKfQrcode = $db->set_table('ylb_kamiConfig')->findAll(['id' => 1]);
	
    // 获取结果
	if($getKfQrcode){
	    
	    // 获取成功
		$result = array(
		    'kfQrcode' => $getKfQrcode[0]['kmConf_kfQrcode'],
		    'code' => 200,
		    'msg' => '获取成功',
		);
	}else{
	    
	    // 获取失败
        $result = array(
            'code' => 204,
            'msg' => '没有客服二维码',
        );
	}

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>