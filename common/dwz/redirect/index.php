<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
<?php

// 页面编码
header("Content-type:text/html;charset=utf-8");

// 获取参数
$key = trim($_GET['key']);

// 过滤参数
if($key && $key !== ''){
    
    // 数据库配置
    include '../../../console/Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 根据key获取落地域名（落地域名=中转域名）
    $getDwzldym = ['dwz_key'=>$key];
    $getDwzldymResult = $db->set_table('huoma_dwz')->find($getDwzldym);
    if($getDwzldymResult){
        
        // 获取成功
        $dwz_ldym = json_decode(json_encode($getDwzldymResult))->dwz_zzym;
        // 拼接跳转到中转页面的链接
        // 先从当前页面跳转到中转页面
        // 再由中转页面跳转到目标页面
        $longUrl = dirname(dirname($dwz_ldym.$_SERVER['REQUEST_URI'])).'/?key='.$key.'&t='.time();
        // 301跳转
        // header('HTTP/1.1 301 Moved Permanently');
        // 跳转
        header('Location:'.$longUrl);
    }else{
        
        // 获取失败
        echo '<title>温馨提示</title>';
        echo warnningInfo('链接不存在或已被管理员删除');
    }
}else{
    
    // 参数为空
    echo '<title>温馨提示</title>';
    echo warnningInfo('未传入参数');
}

// 提醒文字
function warnningInfo($warnningText){
    
    // 传入$warnningText
    return '<div id="warnning"><img src="../../static/img/warnning.svg" /></div><p id="warnningText">'.$warnningText.'</p>';
    
}
?>
<link rel="stylesheet" href="../../static/css/common.css">