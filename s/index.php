<?php

// 页面编码
header("Content-type:text/html;charset=utf-8");

// 获取参数
$key = trim($_GET['key']);

// 过滤参数
if($key && $key !== ''){
    
    // 数据库配置
    include '../console/Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 目录级别
    $folderNum = $config['folderNum'];

    // 根据key获取落地域名和qun_id
    $getQunldym = ['qun_key'=>$key];
    $getQunldymResult = $db->set_table('huoma_qun')->find($getQunldym);
    if($getQunldymResult){
        
        echo '<title>加载中...</title>';
        
        // 获取成功
        $qun_ldym = json_decode(json_encode($getQunldymResult))->qun_ldym;
        $qun_id = json_decode(json_encode($getQunldymResult))->qun_id;
        
        // 301跳转
        redirectHmPage($folderNum,$qun_ldym,'qun','qid',$qun_id);
        
    }else{
        
        // 获取失败
        // 尝试获取客服码
        // 根据key获取落地域名和kf_id
        $getKfldym = ['kf_key'=>$key];
        $getKfldymResult = $db->set_table('huoma_kf')->find($getKfldym);
        if($getKfldymResult){
            
            echo '<title>加载中...</title>';
            
            // 获取成功
            $kf_ldym = json_decode(json_encode($getKfldymResult))->kf_ldym;
            $kf_id = json_decode(json_encode($getKfldymResult))->kf_id;
          
            // 301跳转
            redirectHmPage($folderNum,$kf_ldym,'kf','kid',$kf_id);
        
        }else{
            
            // 获取失败
            // 尝试获取渠道码
            // 根据key获取落地域名和kf_id
            $getChannelldym = ['channel_key'=>$key];
            $getChannelldymResult = $db->set_table('huoma_channel')->find($getChannelldym);
            if($getChannelldymResult){
                
                echo '<title>加载中...</title>';
                
                // 获取成功
                $channel_ldym = json_decode(json_encode($getChannelldymResult))->channel_ldym;
                $channel_id = json_decode(json_encode($getChannelldymResult))->channel_id;
                
                // 301跳转
                redirectHmPage($folderNum,$channel_ldym,'channel','cid',$channel_id);
                
            }else{
                
                // 获取失败
                echo '<title>温馨提示</title>';
                echo warnningInfo('链接不存在或已被管理员删除');
            }
        }
        
    }
}else{
    
    // 参数为空
    echo '<title>温馨提示</title>';
    echo warnningInfo('请求参数为空');
}

// 跳转到落地页
function redirectHmPage($folderNum,$ldym,$hmType,$hmidName,$hmid){
    
    if($folderNum == 1){
            
        // 根目录
        $longUrl = $ldym.'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid;
    }else{
        
        // 其他目录
        $longUrl = $ldym.'/'.redirectURL($folderNum).'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid;
    }
    
    // 301跳转
    header('HTTP/1.1 301 Moved Permanently');
    
    // 跳转
    header('Location:'.$longUrl);
}

// 目录级别
function redirectURL($folderNum){
    
    if($folderNum == 2){
        
        // 二级目录（跟目录下的一个目录）
        // 假设根目录为wwwroot/
        // 活码系统代码放在wwwroot/huoma/
        // 那么/huoma/这个就是二级目录
        return basename(dirname(dirname(__FILE__)));
    }else if($folderNum == 3){
        
        // 三级目录（跟目录下的一个目录里面的一个目录）
        // 假设根目录名wwwroot/
        // 活码系统代码放在wwwroot/tool/huoma/
        // 那么tool/这个就是二级目录，huoma/就是三级目录
        return basename(dirname(dirname(dirname(__FILE__)))).'/'.basename(dirname(dirname(__FILE__)));
    }else if($folderNum == 4){
        
        // 四级目录（跟目录/二级目录/三级目录/四级目录）
        // 假设根目录名wwwroot/
        // 活码系统代码放在wwwroot/wx/tool/huoma/
        // 那么wx/是二级目录，tool/是三级目录，huoma/是四级目录
        $oneFolder = basename(dirname(dirname(dirname(dirname(__FILE__))))).'/';
        $twoFolder = basename(dirname(dirname(dirname(__FILE__)))).'/';
        $threeFolder = basename(dirname(dirname(__FILE__)));
        return $oneFolder.$twoFolder.$threeFolder;
    }
}

// 提醒文字
function warnningInfo($warnningText){
    
    // 传入$warnningText
    return '<div id="warnning"><img src="../static/img/warnning.svg" /></div><p id="warnningText">'.$warnningText.'</p>';
    
}

?>
<link rel="stylesheet" href="../static/css/common.css">