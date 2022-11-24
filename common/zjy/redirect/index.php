<?php

// 获取参数
$zid = trim(intval($_GET['zid']));

// 过滤参数
if($zid && $zid !== ''){
    
    // 数据库配置
    include '../../../console/Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 根据zid获取落地域名
    $getZjyldym = ['zjy_id'=>$zid];
    $getZjyldymResult = $db->set_table('huoma_tbk')->find($getZjyldym);
    if($getZjyldymResult){
        
        // 获取成功
        $zjy_ldym = json_decode(json_encode($getZjyldymResult))->zjy_ldym;
        
        // 拼接落地页链接
        $longUrl = dirname(dirname($zjy_ldym.$_SERVER['REQUEST_URI'])).'/?zid='.$zid;
        
        // 301跳转
        header('HTTP/1.1 301 Moved Permanently');
        
        // 跳转
        header('Location:'.$longUrl);
    }else{
        
        // 获取失败
        echo '页面不存在或已被管理员删除';
    }
}else{
    
    // 参数为空
    echo '请求参数为空';
}

?>