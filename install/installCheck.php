<?php
    
    // 编码
    header("Content-type: application/json");
    
    // 关闭错误
    ini_set("display_errors", "Off");
    
    // 测试文件
    $consoleTestFile = "../console/test.txt";
    $uploadTestFile = "../console/upload/test.txt";
    
    // 判断创建测试文件结果
    if (file_put_contents($consoleTestFile, "上传测试文件，可删除.") && file_put_contents($uploadTestFile, "上传测试文件，可删除.")) {
        
        // 创建成功
        $uploadResult = '获得上传权限';
        $uploadResultText = '<span style="color:#07C160;font-weight:bold;">✓符合<span>';
    } else {
        
        // 创建失败
        $uploadResult = '没有上传权限';
        $uploadResultText = '<span style="color:#f00;font-weight:bold;">不符合<span>';
    }
    
    // 获取PHP版本
    $phpVersion = phpversion();
    if($phpVersion >= 7.0 && $phpVersion <= 7.5) {
        $php_version_result_text = '<span style="color:#07C160;font-weight:bold;">✓符合<span>';
    }else{
        $php_version_result_text = '<span style="color:#f00;font-weight:bold;">不符合<span>';
    }
    
    // 结果
    $result = array(
        'code' => 200,
        'msg' => '检测完成',
        'php_version' => $phpVersion,
        'php_version_result_text' => $php_version_result_text,
        'upload_result' => $uploadResult,
        'upload_result_text' => $uploadResultText,
    );
    
    // 输出结果
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
    // 删除测试文件
    unlink($consoleTestFile);
    unlink($uploadTestFile);
    
?>
