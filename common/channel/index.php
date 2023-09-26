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
    </head>
<body>
    
<?php

    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数
    $cid = trim(intval($_GET['cid']));
    
    // 过滤参数
    if($cid){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取渠道码信息
        $getChannelInfo = ['channel_id'=>$cid];
        $getChannelInfoResult = $db->set_table('huoma_channel')->find($getChannelInfo);
        
        // 验证该渠道码是否存在
        if($getChannelInfoResult && $getChannelInfoResult > 0){
            
            // 存在
            // 解析所需字段
            $channel_status = getSqlData($getChannelInfoResult,'channel_status');
            $channel_url = getSqlData($getChannelInfoResult,'channel_url');
            
            // 判断该渠道码的状态
            if($channel_status == 1){
                
                // 当前状态：正常
                // 更新渠道码的总访问量
                updateChannelPv($db,$cid);
                
                // 更新渠道码的今天访问量
                updateChannelTodayPv($db,$cid);
                
                // 更新当前小时的总访问量
                updateCurrentHourPageView($db,'channel');
                
                // 记录今天ip访问量
                updateTodayIpNum($db);
                
                // 来源操作系统
                $data_device = getSystem();
                
                // 来源APP
                $data_referer = getAppName();
                
                // 来源ip
                $data_ip = $_SERVER['REMOTE_ADDR'];
                
                // 查询该IP是否被加入黑名单
                $checkThisIpIsAccessDenied = $db->set_table('huoma_channel_accessdenied')->find(['data_ip'=>$data_ip]);
                if($checkThisIpIsAccessDenied){
                    
                    // 在黑名单里
                    echo warnInfo('温馨提示','你被管理员设为禁止访问');
                }else{
                    
                    // 不在黑名单
                    // 查询数据库是否有一致的来源数据
                    $checkTheSameData = [
                        'data_referer'=>$data_referer,
                        'data_device'=>$data_device,
                        'data_ip'=>$data_ip,
                        'channel_id'=>$cid
                    ];
                    
                    // 查询
                    $checkTheSameDataResult = $db->set_table('huoma_channel_data')->find($checkTheSameData);
                    if($checkTheSameDataResult){
                        
                        // 有一样的来源数据
                        // 更新当前来源数据的访问量
                        $data_id = json_decode(json_encode($checkTheSameDataResult))->data_id;
                        updateThisChannelDataPv($db,$data_id,$channel_url);
                    }else{
                        
                        // 没有一样的来源数据
                        // 将来源信息存入数据库
                        $data_id = rand(100000,999999);
                        $saveRefererInfo = [
                            'channel_id'=>$cid,
                            'data_id'=>$data_id,
                            'data_referer'=>$data_referer,
                            'data_device'=>$data_device,
                            'data_ip'=>$data_ip
                        ];
                        $saveRefererInfoResult = $db->set_table('huoma_channel_data')->add($saveRefererInfo);
                        
                        // 更新当前来源数据的访问量
                        updateThisChannelDataPv($db,$data_id,$channel_url);
                    }
                }
                
            }else{
                
                // 当前状态：停用
                // channel_status !== 1的情况
                echo warnInfo('温馨提示','页面已被管理员暂停使用');
            } // if($channel_status == 1)
        }else{
            
            // 不存在
            // 获取不到该channel_id的详情
            echo warnInfo('温馨提示','页面不存在或已被管理员删除');
        } // if($getChannelInfoResult && $getChannelInfoResult > 0)
        
    } // if($cid && $cid !== '')
    
    /**
     * 以下是封装的一些操作函数
     * 一方面是便于多处调用
     * 另一方面是保持代码的整洁可读性
     */
     
    // 记录今天ip访问量
    function updateTodayIpNum($db){
        
        // 获取ip地址
        $getIP = $_SERVER['REMOTE_ADDR'];
        
        // 获取今天的ip记录数
        $getTodayIpNum = $db->set_table('huoma_ip')->find(['ip_create_time'=>date('Y-m-d')]);
        
        // 如果有记录
        if($getTodayIpNum){
            
            // 查询当前ip是否为今天首次访问
            $getThisIpISFirstTimeToday = $db->set_table('huoma_ip_temp')->find(['create_date'=>date('Y-m-d'),'ip'=>$getIP,'from_page'=>'channel']);
            
            // 如果没有记录
            // 说明这个ip是今天第一次访问
            if(!$getThisIpISFirstTimeToday){
                
                // 将当前ip添加至临时ip表
                $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'channel']);
                
                // 然后更新今天的ip记录数
                $channel_ip = json_decode(json_encode($getTodayIpNum))->channel_ip;
                $newChannel_ip = $channel_ip + 1;
                $db->set_table('huoma_ip')->update(['ip_create_time'=>date('Y-m-d')],['channel_ip'=>$newChannel_ip]);
            }
        }else{
            
            // 如果没有记录
            // 将当前ip添加至临时ip表并记录为今天的ip访问
            $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'channel']);
            
            // 新增这个ip今天的访问次数
            $db->set_table('huoma_ip')->add(['channel_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
        }
        
        // 昨天的日期
        $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
        
        // 检查是否存在昨天的ip记录
        $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(['create_date'=>$yesterdayDate,'from_page'=>'channel']);
        
        // 如果有记录
        if($getYesterdayIp){
            
            // 清理昨天日期的临时ip
            $db->set_table('huoma_ip_temp')->delete(['create_date'=>$yesterdayDate,'from_page'=>'channel']);
        }
    }
    
    // 更新渠道码的今天访问量
    function updateChannelTodayPv($db,$cid){
        
        // 获取channel_today_pv字段并提取pv和date
        $getTodayChannelPv = $db->set_table('huoma_channel')->find(['channel_id'=>$cid]);
        if($getTodayChannelPv){
            
            // channel_today_pv的值
            $channel_today_pv = getSqlData($getTodayChannelPv,'channel_today_pv');
            
            // pv的值
            $today_pv = json_decode($channel_today_pv,true)['pv'];
            
            // date的值
            $today_date = json_decode($channel_today_pv,true)['date'];
            
            // 检查这个记录是不是今天的
            if($today_date == date('Y-m-d')){
                
                // 如果是今天的
                // 更新pv的值
                $newToday_pv = $today_pv + 1;
                $db->set_table('huoma_channel')->update(
                    ['channel_id'=>$cid],
                    ['channel_today_pv'=>'{"pv":"'.$newToday_pv.'","date":"'.date('Y-m-d').'"}']
                );
            }else{
                
                // 如果不是今天的
                // 先将日期更新为今天的
                // 再更新今天pv的值
                $db->set_table('huoma_channel')->update(
                    ['channel_id'=>$cid],
                    ['channel_today_pv'=>'{"pv":"1","date":"'.date('Y-m-d').'"}']
                );
            }
        }
    }
    
    // 更新当前小时的总访问量
    function updateCurrentHourPageView($db,$hourNum_type){
        
        // 引入公共文件
        include '../../console/public/updateCurrentHourPageView.php';
    }
    
    // 更新当前渠道码的总访问量
    function updateChannelPv($db,$cid){
        
        $updateChannelPv = 'UPDATE huoma_channel SET channel_pv=channel_pv+1 WHERE channel_id="'.$cid.'"';
        $db->set_table('huoma_channel')->findSql($updateChannelPv);
    }
    
    // 更新渠道码单条来源数据的访问量
    function updateThisChannelDataPv($db,$data_id,$channel_url){

        // 传入data_id
        $data_creat_time = date('Y-m-d H:i:s');
        $updateThisChannelDataPv = 'UPDATE huoma_channel_data SET data_pv=data_pv+1, data_creat_time="'.$data_creat_time.'" WHERE data_id="'.$data_id.'"';
        $updateThisChannelDataPvResult = $db->set_table('huoma_channel_data')->findSql($updateThisChannelDataPv);
        if($updateThisChannelDataPvResult){
            
            // 跳转到目标页
            header('Location:'.$channel_url);
        }else{
            
            // 跳转到目标页
            header('Location:'.$channel_url);
        }
    }
    
    // 解析数组
    function getSqlData($result,$field){
        
        // 传入数组和需要解析的字段
        return json_decode(json_encode($result))->$field;
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
    
    // 获取当前访问设备的操作系统
    function getSystem(){
        
        // 根据HTTP访问来获取操作系统
        $HTTP_DEVICE = $_SERVER['HTTP_USER_AGENT'];
        
        if(preg_match('/Windows/i',$HTTP_DEVICE)){
            
            // Windows
            return $DEVICE_SYSTEM = 'Windows';
        }else if(preg_match('/Android/i',$HTTP_DEVICE)){
            
            // Andriod
            return $DEVICE_SYSTEM = 'Android';
        }else if(preg_match('/iPad/i',$HTTP_DEVICE)){
            
            // iPad
            return $DEVICE_SYSTEM = 'iPad';
        }else if(preg_match('/iPhone/i',$HTTP_DEVICE)){
            
            // iOS
            return $DEVICE_SYSTEM = 'iOS';
        }else if(preg_match('/Macintosh/i',$HTTP_DEVICE)){
            
            // Mac
            return $DEVICE_SYSTEM = 'Mac';
        }else if(preg_match('/X11/i',$HTTP_DEVICE)){
            
            // Linux
            return $DEVICE_SYSTEM = 'Linux';
        }else{
            
            // 其它操作系统
            return $DEVICE_SYSTEM = '未知设备';
        }
    }
    
    // 获取当前访问环境所处的APP
    function getAppName(){
        
        // 根据HTTP访问来获取所处的APP
        $HTTP_DEVICE = $_SERVER['HTTP_USER_AGENT'];
        
        if(preg_match('/MicroMessenger/i',$HTTP_DEVICE)){

            // 微信
            return $DEVICE_BROWSER = '微信';
        }else if(preg_match('/Weibo/i',$HTTP_DEVICE)){

            // 微博
            return $DEVICE_BROWSER = '微博';
        }else if(preg_match('/BiliApp/i',$HTTP_DEVICE)){

            // 哔哩哔哩
            return $DEVICE_BROWSER = '哔哩哔哩';
        }else if(preg_match('/QQ/i',$HTTP_DEVICE)){

            // QQ
            return $DEVICE_BROWSER = 'QQ';
        }else if(preg_match('/AlipayClient/i',$HTTP_DEVICE)){

            // 支付宝
            return $DEVICE_BROWSER = '支付宝';
        }else if(preg_match('/baiduboxapp/i',$HTTP_DEVICE)){

            // 百度
            return $DEVICE_BROWSER = '百度';
        }else if(preg_match('/DingTalk/i',$HTTP_DEVICE)){

            // 钉钉
            return $DEVICE_BROWSER = '钉钉';
        }else if(preg_match('/QQBrowser/i',$HTTP_DEVICE)){

            // QQ浏览器
            return $DEVICE_BROWSER = 'QQ浏览器';
        }else if(preg_match('/MiuiBrowser/i',$HTTP_DEVICE)){

            // 小米浏览器
            return $DEVICE_BROWSER = '小米浏览器';
        }else if(preg_match('/VivoBrowser/i',$HTTP_DEVICE)){

            // vivo浏览器
            return $DEVICE_BROWSER = 'vivo浏览器';
        }else if(preg_match('/HUAWEI/i',$HTTP_DEVICE)){

            // 华为浏览器
            return $DEVICE_BROWSER = '华为浏览器';
        }else if(preg_match('/OPPO/i',$HTTP_DEVICE)){

            // OPPO浏览器
            return $DEVICE_BROWSER = 'OPPO浏览器';
        }else if(preg_match('/HONOR/i',$HTTP_DEVICE)){

            // 荣耀浏览器
            return $DEVICE_BROWSER = '荣耀浏览器';
        }else if(preg_match('/ONEPLUS/i',$HTTP_DEVICE)){

            // 一加浏览器
            return $DEVICE_BROWSER = '一加浏览器';
        }else if(preg_match('/Redmi/i',$HTTP_DEVICE)){

            // 红米浏览器
            return $DEVICE_BROWSER = '红米浏览器';
        }else if(preg_match('/UCBrowser/i',$HTTP_DEVICE)){
            
            // UC浏览器
            return $DEVICE_BROWSER = 'UC浏览器';
        }else if(preg_match('/Chrome/i',$HTTP_DEVICE)){
            
            // Chrome内核浏览器
            return $DEVICE_BROWSER = 'Chrome内核浏览器';
        }else{
            
            // 未知APP
            return $DEVICE_BROWSER = '未知APP';
        }
    }

?>

</body>
</html>