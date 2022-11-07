<?php

// 获取参数
$qid = trim(intval($_GET['qid']));

// 过滤参数
if($qid && $qid !== ''){
    
    // 数据库配置
    include '../../../console/Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 数据库huoma_qun表
    $huoma_qun = $db->set_table('huoma_qun');
    
    // 根据qid获取落地域名
    $getQunldym = ['qun_id'=>$qid];
    $getQunldymResult = $huoma_qun->find($getQunldym);
    if($getQunldymResult){
        
        // 获取成功
        $qun_ldym = json_decode(json_encode($getQunldymResult))->qun_ldym;
        // 拼接落地页链接
        $longUrl = dirname(dirname($qun_ldym.$_SERVER['REQUEST_URI'])).'/?qid='.$qid;
        // 301跳转
        header('HTTP/1.1 301 Moved Permanently');
        // 跳转
        header('Location:'.$longUrl);
    }else{
        
        // 获取失败
        echo '该群不存在或已被管理员删除';
    }
}else{
    
    // 参数为空
    echo '请求参数为空';
}

?>