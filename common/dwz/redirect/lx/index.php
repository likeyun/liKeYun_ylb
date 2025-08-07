<?php
    
    // 获取Key
    $key = $_GET['key'];
    
    // 跳转到落地页
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ../../?key=' . $key . '&t=' . time());
?>