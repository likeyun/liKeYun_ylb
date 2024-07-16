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
	
	$result = array(
	    'code' => 204,
        'msg' => '该功能为收费功能，价格19.90元，请联系微信sansure2016'
    );

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
