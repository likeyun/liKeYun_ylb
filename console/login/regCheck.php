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
        'msg' => '该功能为收费功能，请访问 <a href="https://afdian.net/item/207e8118ac6c11ee89a85254001e7c00" target="blank">https://afdian.net/item/207e8118ac6c11ee89a85254001e7c00</a> 付费购买源码后使用。'
    );

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>