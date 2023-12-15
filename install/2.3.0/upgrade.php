<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 读取文件内容
    $fileContents = file_get_contents('../../console/Db.php');
    
    // 新的版本号
    $newVersion = '2.3.0';
    
    // 使用正则表达式替换旧版本号
    $updatedContents = preg_replace("/'version' => '[^']*'/", "'version' => '$newVersion'", $fileContents);
    
    // 将更新后的内容写回文件
    file_put_contents('../../console/Db.php', $updatedContents);
    
    // 升级成功
    $result = array(
        'code' => 200,
        'msg' => '升级完成！'
    );
    
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>