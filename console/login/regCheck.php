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
        'msg' => '注册功能为收费功能，网站管理员未购买注册功能的相关代码，请购买，价格19.90元，购买地址：https://viusosibp88.feishu.cn/docx/Tot8dTJJIoDw4Px1nlsc6oH9n5g'
    );

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
