<?php

    // 本地文件
    $file = '../app.json';
    
    // 读取原始 JSON 内容，保留格式
    $rawJson = file_get_contents($file);
    $data = json_decode($rawJson, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("JSON 解析失败: " . json_last_error_msg());
    }
    
    // 要修改的项
    $data['version'] = '2.4.1'; // 新版本号
    $data['name'] = '微信外链' . $data['version']; // 名称
    
    // 使用 JSON_PRETTY_PRINT 和 JSON_UNESCAPED_UNICODE 保留格式与中文
    $newJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // 写回文件
    file_put_contents($file, $newJson);

?>
