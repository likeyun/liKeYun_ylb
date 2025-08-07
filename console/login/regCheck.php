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
        'msg' => '网站管理员未开通注册功能，如果你是管理员，请获得相关代码进行开通：https://afdian.com/item/38402d64c20b11efb0515254001e7c00'
    );

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
