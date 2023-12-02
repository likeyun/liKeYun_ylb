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
	
    // 注销登录
    session_start();
    unset($_SESSION["yinliubao"]);
    
    // 注销结果
    $result = array(
        'code' => 200,
        'msg' => '已退出'
    );
    
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>