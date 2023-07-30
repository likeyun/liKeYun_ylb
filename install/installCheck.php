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
	ini_set("display_errors", "Off");
	
    // 获取PHP版本
    $php_version = phpversion();
    
    // 创建文件以检测上传权限
    file_put_contents("../console/test.txt","上传测试文件，可删除。");
    file_put_contents("../console/upload/test.txt","上传测试文件，可删除。");
    
    // 获取创建结果
    $getCreatResult_console = file_get_contents("../console/test.txt");
    $getCreatResult_upload = file_get_contents("../console/upload/test.txt");
    
    if($getCreatResult_console && $getCreatResult_upload){
        
        // 创建成功，上传权限检测通过
        $result = array(
            'code' => 200,
            'msg' => '检测完成',
            'php_version' => $php_version,
            'upload_result' => 1
        );
    }else{
        
        // 创建失败，上传权限检测不通过
        $result = array(
            'code' => 200,
            'msg' => '检测完成',
            'php_version' => $php_version,
            'upload_result' => 2
        );
    }
    
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
    // 删除测试文件
    unlink("../console/test.txt");
    unlink("../console/upload/test.txt");
?>