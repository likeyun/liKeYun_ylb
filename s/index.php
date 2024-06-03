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
    
    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
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
    
        // 根据key获取群活码信息
        $getQunInfo = $db->set_table('huoma_qun')->find(['qun_key'=>$key]);
        if($getQunInfo){
            
            echo '<title>加载中...</title>';
            
            // 获取成功
            $qun_rkym = json_decode(json_encode($getQunInfo))->qun_rkym; // 入口域名
            $qun_id = json_decode(json_encode($getQunInfo))->qun_id;
            
            // 用入口域名跳转
            jumpTo($folderNum,$qun_rkym,'qun','qid',$qun_id);
            
        }else{
            
            // 根据key获取客服码信息
            $getKefuInfo = $db->set_table('huoma_kf')->find(['kf_key'=>$key]);
            if($getKefuInfo){
                
                echo '<title>加载中...</title>';
                
                // 获取成功
                $kf_rkym = json_decode(json_encode($getKefuInfo))->kf_rkym; // 入口域名
                $kf_id = json_decode(json_encode($getKefuInfo))->kf_id;
              
                // 用入口域名跳转
                jumpTo($folderNum,$kf_rkym,'kf','kid',$kf_id);
            
            }else{
                
                // 根据key获取渠道码信息
                $getChannelInfo = $db->set_table('huoma_channel')->find(['channel_key'=>$key]);
                if($getChannelInfo){
                    
                    echo '<title>加载中...</title>';
                    
                    // 获取成功
                    $channel_rkym = json_decode(json_encode($getChannelInfo))->channel_rkym; // 入口域名
                    $channel_id = json_decode(json_encode($getChannelInfo))->channel_id;
                    
                    // 用入口域名跳转
                    jumpTo($folderNum,$channel_rkym,'channel','cid',$channel_id);
                    
                }else{
                    
                    // 根据key获取中间页信息
                    $getZjyInfo = $db->set_table('huoma_tbk')->find(['zjy_key'=>$key]);
                    if($getZjyInfo){
                        
                        echo '<title>加载中...</title>';
                        
                        // 获取成功
                        $zjy_rkym = json_decode(json_encode($getZjyInfo))->zjy_rkym; // 入口域名
                        $zjy_id = json_decode(json_encode($getZjyInfo))->zjy_id;
                        
                        // 用入口域名跳转
                        jumpTo($folderNum,$zjy_rkym,'zjy','zid',$zjy_id);
                        
                    }else{
                        
                        // 根据key获取多项单页信息
                        $getMultiSPAInfo = $db->set_table('huoma_tbk_mutiSPA')->find(['multiSPA_key'=>$key]);
                        if($getMultiSPAInfo){
                            
                            echo '<title>加载中...</title>';
                            
                            // 获取成功
                            $multiSPA_rkym = json_decode(json_encode($getMultiSPAInfo))->multiSPA_rkym; // 入口域名
                            $multiSPA_id = json_decode(json_encode($getMultiSPAInfo))->multiSPA_id;
                            
                            // 用入口域名跳转
                            jumpTo($folderNum,$multiSPA_rkym,'multiSPA','mid',$multiSPA_id);
                            
                        }else{
                            
                            // 获取失败
                            // 这里要判断这个Key是否进行了并流
                            // 1. 根据这个Key去查询并流表
                            $getbingliuForKey = $db->set_table('ylb_qun_bingliu')->find(['before_qun_key' => $key]);
                            if($getbingliuForKey) {
                                
                                // 确定加入了并流
                                // 获取并流的开启状态
                                $bingliu_status = $getbingliuForKey['bingliu_status'];
                                // 获取并入的活码id
                                $later_qun_id = $getbingliuForKey['later_qun_id'];
                                // 获取并流次数
                                $bingliu_num = $getbingliuForKey['bingliu_num'];
                                
                                // 获取并入的活码的入口域名
                                $getrkymForLaterQunId = $db->set_table('huoma_qun')->find(['qun_id' => $later_qun_id]);
                                $laterQun_rkym = $getrkymForLaterQunId['qun_rkym'];
                                
                                if($bingliu_status == 1) {
                                    
                                    // 如果这个并流开启了
                                    // 更新并流次数
                                    $newNum = $bingliu_num + 1;
                                    $db->set_table('ylb_qun_bingliu')->update(
                                        ['before_qun_key' => $key],
                                        ['bingliu_num' => $newNum]
                                    );
                                    
                                    // 然后jumpTo
                                    jumpTo($folderNum,$laterQun_rkym,'qun','qid',$later_qun_id);
                                }else {
                                    
                                    // 不开启
                                    echo warnInfo('温馨提示','链接不存在或已被管理员删除');
                                }
                            }else {
                                
                                // 不在
                                echo warnInfo('温馨提示','链接不存在或已被管理员删除');
                            }
                        }
                    }
                }
            }
            
        }
    }else{
        
        // 参数为空
        echo warnInfo('温馨提示','请求参数为空');
    }
    
    // 跳转到落地页
    function jumpTo($folderNum,$rkym,$hmType,$hmidName,$hmid){
        
        if($folderNum == 1){
                
            // 根目录
            $longUrl = $rkym.'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid.'&t='.time();
        }else{
            
            // 其他目录
            $longUrl = $rkym.'/'.redirectURL($folderNum).'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid.'&t='.time();
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