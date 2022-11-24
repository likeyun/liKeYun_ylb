<?php

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

    // 根据key获取入口域名
    $getDwzrkym = ['dwz_key'=>$key];
    $getDwzrkymResult = $db->set_table('huoma_dwz')->find($getDwzrkym);
    if($getDwzrkymResult){
        
        echo '<title>加载中...</title>';
        
        // 获取成功
        $Dwz_rkym = json_decode(json_encode($getDwzrkymResult))->Dwz_rkym;
        
        // 301跳转
        redirectHmPage($folderNum,$Dwz_rkym,'dwz','key',$key);
        
    }else{
        
        // 参数为空
        echo '<title>温馨提示</title>';
        echo warnningInfo('链接不存在或已被管理员删除');
        echo '<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">';
    }
}else{
    
    // 参数为空
    echo '<title>温馨提示</title>';
    echo warnningInfo('请求参数为空');
}

// 跳转到落地页
function redirectHmPage($folderNum,$rkym,$hmType,$hmidName,$hmid){
    
    if($folderNum == 1){
            
        // 根目录
        $longUrl = $rkym.'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid;
    }else{
        
        // 其他目录
        $longUrl = $rkym.'/'.redirectURL($folderNum).'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid;
    }
    
    // 301跳转
    // header('HTTP/1.1 301 Moved Permanently');
    
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
    return '<div id="warnning"><img src="../../static/img/warnning.svg" /></div><p id="warnningText">'.$warnningText.'</p>';
    
}

?>
<link rel="stylesheet" href="../../static/css/common.css">