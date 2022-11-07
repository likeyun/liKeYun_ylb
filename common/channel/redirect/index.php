<?php

// 获取参数
$cid = trim(intval($_GET['cid']));

// 过滤参数
if($cid && $cid !== ''){
    
    // 数据库配置
    include '../../../console/Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 数据库huoma_channel表
    $huoma_channel = $db->set_table('huoma_channel');
    
    // 根据cid获取落地域名
    $getchannelldym = ['channel_id'=>$cid];
    $getchannelldymResult = $huoma_channel->find($getchannelldym);
    if($getchannelldymResult){
        
        // 获取成功
        $channel_ldym = json_decode(json_encode($getchannelldymResult))->channel_ldym;
        // 拼接落地页链接
        $longUrl = dirname(dirname($channel_ldym.$_SERVER['REQUEST_URI'])).'/?cid='.$cid;
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