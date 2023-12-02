<?php

// 该软件遵循MIT开源协议。
// FAQ跳转调度中心
$faq = trim($_GET['faq']);

// 根据faq类型跳转
if($faq == 'qun'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'kf'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'channel'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'dwz'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'tbk'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'shareCard'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'config'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'user'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'sucai'){
    
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else if($faq == 'default'){
    
    // 默认
    // 跳转链接
    $redUrl = 'https://docs.qq.com/doc/DREdWVGJxeFFOSFhI';
}else{
    
    // 该参数不属于内部定义参数
    echo '该参数不属于内部定义参数，无法根据参数跳转。';
}

// 301跳转
header('HTTP/1.1 301 Moved Permanently');

// 跳转
header('Location:'.$redUrl);

?>