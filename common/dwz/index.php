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

    /**
     * 标题：短网址公共页面
     * 维护：2023年7月28日
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     * 摘要：优化代码结构、新增通知渠道、新增IP记录、UI样式优化
     */

    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数
    $key = trim($_GET['key']);
    
    // 防SQL注入
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
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取短网址信息
        $getDwzMsg = $db->set_table('huoma_dwz')->find(['dwz_key'=>$key]);
        
        // 验证该短网址是否存在
        if($getDwzMsg){
            
            // 解析所需字段
            $dwz_status = getSqlData($getDwzMsg,'dwz_status');
            $dwz_url = getSqlData($getDwzMsg,'dwz_url');
            $dwz_type = getSqlData($getDwzMsg,'dwz_type');
            
            // 根据设备进行跳转的目标链接
            $dwz_android_url = getSqlData($getDwzMsg,'dwz_android_url');
            $dwz_ios_url = getSqlData($getDwzMsg,'dwz_ios_url');
            $dwz_windows_url = getSqlData($getDwzMsg,'dwz_windows_url');
            
            // 判断该短网址的状态
            if($dwz_status == 1){
                
                // 当前状态：正常
                // 更新当前短网址的总访问量
                updateDwzPv($db,$key);
                
                 // 更新当前短网址的今天访问量
                updateDwzTodayPv($db,$key);
                
                // 更新当前小时的总访问量
                updateCurrentHourPageView($db,'dwz');
                
                // 记录今天ip访问量
                updateTodayIpNum($db);
                
                // 根据访问限制进行跳转
                locationUrl($dwz_type,$dwz_url,$dwz_android_url,$dwz_ios_url,$dwz_windows_url);
                
                // 如需增加自己的逻辑可在下方增加
                // ---你的逻辑代码---
                
            }else{
                
                // 当前状态：停用
                // dwz_status !== 1的情况
                echo warnInfo('温馨提示','链接已被管理员暂停使用');
                
                // 如需增加自己的逻辑可在下方增加
                // ---你的逻辑代码---
                
            } // if($dwz_status == 1)
        }else{
            
            // 不存在
            // 获取不到该dwz_key的详情
            echo warnInfo('温馨提示','链接不存在或已被管理员删除');
            
            // 如需增加自己的逻辑可在下方增加
            // ---你的逻辑代码---
                
        } // if($getDwzMsg && $getDwzMsg > 0)
        
    } // if($key && $key !== '')
    
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
            $getThisIpISFirstTimeToday = $db->set_table('huoma_ip_temp')->find(['create_date'=>date('Y-m-d'),'ip'=>$getIP,'from_page'=>'dwz']);
            
            // 如果没有记录
            // 说明这个ip是今天第一次访问
            if(!$getThisIpISFirstTimeToday){
                
                // 将当前ip添加至临时ip表
                $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'dwz']);
                
                // 然后更新今天的ip记录数
                $dwz_ip = json_decode(json_encode($getTodayIpNum))->dwz_ip;
                $newDwz_ip = $dwz_ip + 1;
                $db->set_table('huoma_ip')->update(['ip_create_time'=>date('Y-m-d')],['dwz_ip'=>$newDwz_ip]);
            }
        }else{
            
            // 如果没有记录
            // 将当前ip添加至临时ip表并记录为今天的ip访问
            $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'dwz']);
            
            // 新增这个ip今天的访问次数
            $db->set_table('huoma_ip')->add(['dwz_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
        }
        
        // 昨天的日期
        $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
        
        // 检查是否存在昨天的ip记录
        $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(['create_date'=>$yesterdayDate,'from_page'=>'dwz']);
        
        // 如果有记录
        if($getYesterdayIp){
            
            // 清理昨天日期的临时ip
            $db->set_table('huoma_ip_temp')->delete(['create_date'=>$yesterdayDate,'from_page'=>'dwz']);
        }
    }
    
    // 更新当前短网址的今天访问量
    function updateDwzTodayPv($db,$key){
        
        // 获取dwz_today_pv字段并提取pv和date
        $getTodayDwzPv = $db->set_table('huoma_dwz')->find(['dwz_key'=>$key]);
        if($getTodayDwzPv){
            
            // dwz_today_pv的值
            $dwz_today_pv = getSqlData($getTodayDwzPv,'dwz_today_pv');
            
            // pv的值
            $today_pv = json_decode($dwz_today_pv,true)['pv'];
            
            // date的值
            $today_date = json_decode($dwz_today_pv,true)['date'];
            
            // 检查这个记录是不是今天的
            if($today_date == date('Y-m-d')){
                
                // 如果是今天的
                // 更新pv的值
                $newToday_pv = $today_pv + 1;
                $db->set_table('huoma_dwz')->update(
                    ['dwz_key'=>$key],
                    ['dwz_today_pv'=>'{"pv":"'.$newToday_pv.'","date":"'.date('Y-m-d').'"}']
                );
            }else{
                
                // 如果不是今天的
                // 先将日期更新为今天的
                // 再更新今天pv的值
                $db->set_table('huoma_dwz')->update(
                    ['dwz_key'=>$key],
                    ['dwz_today_pv'=>'{"pv":"1","date":"'.date('Y-m-d').'"}']
                );
            }
        }
    }
    
    // 更新当前小时的总访问量
    function updateCurrentHourPageView($db,$hourNum_type){
        
        // 引入公共文件
        include '../../console/public/updateCurrentHourPageView.php';
    }
    
    // 更新当前短网址的访问量
    function updateDwzPv($db,$key){
        
        $updateDwzPv = 'UPDATE huoma_dwz SET dwz_pv=dwz_pv+1 WHERE dwz_key="'.$key.'"';
        $db->set_table('huoma_dwz')->findSql($updateDwzPv);
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
    
    // 访问限制
    // 类型、通用目标URL、安卓设备目标链接、iOS设备目标链接、Windows设备目标链接
    function locationUrl($dwz_type,$dwz_url,$dwz_android_url,$dwz_ios_url,$dwz_windows_url){
        
        // 不限制
        if($dwz_type == 1){
            
            // 301跳转
            header('HTTP/1.1 301 Moved Permanently');
            
            // 跳转
            header('Location:'.$dwz_url);
        }else if($dwz_type == 2){
            
            // 仅限微信内访问
            if(preg_match('/MicroMessenger/i',$_SERVER['HTTP_USER_AGENT'])){
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }else{
                
                echo warnInfo('温馨提示','仅限微信内访问');
                
                // 以下是未在微信中打开短网址的时候
                // 展示二维码引导用户截图去微信扫码
                // 如果你不需要就注释下面的三行代码
                // 在echo前面添加“//”即可注释
                // echo '<div id="jietuQrcode" style="width:200px;height:200px;margin:20px auto 0;"></div>';
                // echo '<p style="text-align:center;font-size:14px;margin-top:10px;">请截图后在微信扫码打开</p>';
                // echo '<script>new QRCode(document.getElementById("jietuQrcode"), "'.$dwz_url.'")</script>';
                
                // 如需增加自己的逻辑可在下方增加
                // ---你的逻辑代码---
            }
        }else if($dwz_type == 3){

            // 仅限iOS设备访问
            if(preg_match('/iPhone/i',$_SERVER['HTTP_USER_AGENT'])){
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }else{
                
                echo warnInfo('温馨提示','仅限iOS设备访问');
                
                // 如需增加自己的逻辑可在下方增加
                // ---你的逻辑代码---
            }
        }else if($dwz_type == 4){
            
            // 仅限Android设备访问
            if(preg_match('/Android/i',$_SERVER['HTTP_USER_AGENT'])){
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }else{
                
                echo warnInfo('温馨提示','仅限Android设备访问');
                
                // 如需增加自己的逻辑可在下方增加
                // ---你的逻辑代码---
            }
        }else if($dwz_type == 5){
            
            // 仅限手机浏览器访问
            if(
                preg_match('/MicroMessenger/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/Windows/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/Macintosh/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/X11/i',$_SERVER['HTTP_USER_AGENT'])){
                
                echo warnInfo('温馨提示','仅限手机浏览器访问');
                
                // 如需修改为引导图，请在以下添加引导图的代码
                // 请删除上方第267行代码
                // 然后将下方272行代码的“//”取消掉
                // echo '<img src="引导图URL" style="width:100%;" />';
                
                // 如需增加自己的逻辑可在下方增加
                // ---你的逻辑代码---
                
            }else{
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }
        }else if($dwz_type == 6){
            
            // 通过获取屏幕分辨率宽度
            // 判断当前的设备
            $screenWidth = '<script>document.write(window.screen.width);</script>';
            
            // 仅限PC访问
            if(
                preg_match('/Windows/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/Macintosh/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/X11/i',$_SERVER['HTTP_USER_AGENT'])){
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }else{
                
                echo warnInfo('温馨提示','仅限电脑访问');
                
                // 如需增加自己的逻辑可在下方增加
                // ---你的逻辑代码---
            }
        }else{
            
            echo warnInfo('温馨提示','程序发生错误');
            
            // 如需增加自己的逻辑可在下方增加
            // ---你的逻辑代码---
        }
    }

?>

</body>
</html>