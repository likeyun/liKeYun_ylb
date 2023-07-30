<html>
    <head>
        <meta name="wechat-enable-text-zoom-em" content="true">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="color-scheme" content="light dark">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <link rel="shortcut icon" href="https://res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico">
        <link rel="stylesheet" href="../../static/css/common.css">
        <link rel="stylesheet" href="../../static/css/bootstrap.min.css">
        <script type="text/javascript" src="../../static/js/qrcode.min.js"></script>
    </head>
    <body>
        
    <?php
    
    // 获取参数
    $key = trim($_GET['key']);
    
    // 防止SQL注入
    if(preg_match('/[_\-\/\[\].,:;\'"=+*`~!@#$%^&()]/',$key)){
       
        echo warnInfo('温馨提示','该链接不安全，请重新生成！');
        exit;
    }
    
    if(preg_match('/(select|update|drop|DROP|insert|create|delete|where|join|script)/i',$key)){
       
        echo warnInfo('温馨提示','该链接不安全，请重新生成！');
        exit;
    }
    
    // 过滤参数
    if($key){
    
        // 数据库配置
        include '../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 目录级别
        $folderNum = $config['folderNum'];
    
        // 根据key获取入口域名
        $getDwzInfo = $db->set_table('huoma_dwz')->find(['dwz_key'=>$key]);
        if($getDwzInfo){
            
            echo '<title>加载中...</title>';
            
            // 获取成功
            $dwz_rkym = json_decode(json_encode($getDwzInfo))->dwz_rkym;
            
            // 跳转到中转页
            redirectHmPage($folderNum,$dwz_rkym,'dwz','key',$key);
            
        }else{
            
            // 参数为空
            echo warnInfo('温馨提示','链接不存在或已被管理员删除');
        }
    }else{
        
        // 参数为空
        echo warnInfo('温馨提示','请求参数为空');
    }
    
    // 跳转到中转页
    function redirectHmPage($folderNum,$rkym,$hmType,$hmidName,$hmid){
        
        if($folderNum == 1){
                
            // 根目录
            $longUrl = $rkym.'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid.'&t='.time();
        }else{
            
            // 其他目录
            $longUrl = $rkym.'/'.redirectURL($folderNum).'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid.'&t='.time();
        }
        
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
    function warnInfo($title,$warnText){
        
        return '
        <title>'.$title.'</title>
        <div id="warnning">
            <img src="../../../static/img/warn.png" />
        </div>
        <p id="warnText">'.$warnText.'</p>';
    }
    
    ?>
    
    </body>
</html>
    