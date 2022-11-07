<?php

// 获取参数
$kid = trim(intval($_GET['kid']));

// 过滤参数
if($kid && $kid !== ''){
    
    // 数据库配置
    include '../../../console/Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 数据库huoma_kf表
    $huoma_kf = $db->set_table('huoma_kf');
    
    // 根据kid获取落地域名
    $getkfldym = ['kf_id'=>$kid];
    $getkfldymResult = $huoma_kf->find($getkfldym);
    if($getkfldymResult){
        
        // 获取成功
        $kf_ldym = json_decode(json_encode($getkfldymResult))->kf_ldym;
        // 拼接落地页链接
        $longUrl = dirname(dirname($kf_ldym.$_SERVER['REQUEST_URI'])).'/?kid='.$kid;
        // 301跳转
        header('HTTP/1.1 301 Moved Permanently');
        // 跳转
        header('Location:'.$longUrl);
    }else{
        
        // 获取失败
        echo '客服不存在或已被管理员删除';
    }
}else{
    
    // 参数为空
    echo '请求参数为空';
}

?>