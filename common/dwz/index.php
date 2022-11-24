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
        <!--样式文件-->
        <link rel="stylesheet" href="../../static/css/common.css">
        <link rel="stylesheet" href="../../static/css/bootstrap.min.css">
    </head>
<body>
    
<?php

    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数
    $key = trim($_GET['key']);
    
    // 过滤参数
    if($key && $key !== ''){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取短网址信息
        $getDwzInfo = ['dwz_key'=>$key];
        $getDwzInfoResult = $db->set_table('huoma_dwz')->find($getDwzInfo);
        
        // 验证该短网址是否存在
        if($getDwzInfoResult && $getDwzInfoResult > 0){
            
            // 存在
            // 解析所需字段
            $dwz_status = getSqlData($getDwzInfoResult,'dwz_status');
            $dwz_url = getSqlData($getDwzInfoResult,'dwz_url');
            $dwz_type = getSqlData($getDwzInfoResult,'dwz_type');
            
            // 根据设备进行跳转的目标链接
            $dwz_android_url = getSqlData($getDwzInfoResult,'dwz_android_url');
            $dwz_ios_url = getSqlData($getDwzInfoResult,'dwz_ios_url');
            $dwz_windows_url = getSqlData($getDwzInfoResult,'dwz_windows_url');
            
            // 判断该短网址的状态
            if($dwz_status == 1){
                
                // 当前状态：正常
                // 更新当前短网址的访问量
                updateThisDwzPv($db,$key);
                
                // 更新数据统计表访问量
                updateCountChartPv($db);
                
                // 根据访问限制进行跳转
                locationUrl($dwz_type,$dwz_url,$dwz_android_url,$dwz_ios_url,$dwz_windows_url);
                
            }else{
                
                // 当前状态：停用
                // dwz_status !== 1的情况
                echo '<title>温馨提示</title>';
                echo warnningInfo('链接已被管理员暂停使用');
            } // if($dwz_status == 1)
        }else{
            
            // 不存在
            // 获取不到该dwz_key的详情
            echo '<title>温馨提示</title>';
            echo warnningInfo('链接不存在或已被管理员删除');
        } // if($getDwzInfoResult && $getDwzInfoResult > 0)
        
    } // if($key && $key !== '')
    
    /**
     * 以下是封装的一些操作函数
     * 一方面是便于多处调用
     * 另一方面是保持代码的整洁可读性
     */
     
    // 更新数据统计表
    function updateCountChartPv($db){
        
        // 更新数据统计表
        // 数据库huoma_count表
        $huoma_count = $db->set_table('huoma_count');
        
        // 先检查一下当前统计表的数据是不是今天的
        $checkCountData = ['id'=>1];
        $checkCountDataResult = $huoma_count->find($checkCountData);
        
        // 统计表第一条数据当前的日期
        $count_date = json_decode(json_encode($checkCountDataResult))->count_date;
        
        // 判断日期是否为今天的
        if($count_date == date('Y-m-d')){
            
            // 今天
            // 更新当前小时的访问量
            updateThisHourPv($huoma_count);
            
        }else{
            
            // 非今天
            // （1）将日期更新为今天并且访问量归零
            // （2）更新当前小时的访问量
            updateDefault($huoma_count);
        }
    }
    
    // 更新当前小时的访问量
    function updateThisHourPv($huoma_count){
        
        $thisHour = date('H');
        $updatePv = 'UPDATE huoma_count SET count_dwz_pv=count_dwz_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // （1）将日期更新为今天并且访问量归零
    // （2）更新当前小时的访问量
    function updateDefault($huoma_count){
        
        $thisDate = date('Y-m-d');
        $updateDefault = 'UPDATE huoma_count SET count_qun_pv="0",count_kf_pv="0",count_channel_pv="0",count_dwz_pv="0",count_zjy_pv="0",count_date="'.$thisDate.'"';
        $huoma_count->findSql($updateDefault);
        $thisHour = date('H');
        $updatePv = 'UPDATE huoma_count SET count_dwz_pv=count_dwz_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // 更新当前短网址的访问量
    function updateThisDwzPv($db,$key){
        
        // 传入dwz_key
        $updateThisDwzPv = 'UPDATE huoma_dwz SET dwz_pv=dwz_pv+1 WHERE dwz_key="'.$key.'"';
        $db->set_table('huoma_dwz')->findSql($updateThisDwzPv);
    }
    
    // 解析数组
    function getSqlData($result,$field){
        
        // 传入数组和需要解析的字段
        return json_decode(json_encode($result))->$field;
    }
    
    // 提醒文字
    function warnningInfo($warnningText){
        
        // 传入$warnningText
        return '<div id="warnning"><img src="../../static/img/warnning.svg" /></div><p id="warnningText">'.$warnningText.'</p>';
        
    }
    
    // 访问限制
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
                
                echo '<title>温馨提示</title>';
                echo warnningInfo('仅限微信内访问');
            }
        }else if($dwz_type == 3){

            // 仅限iOS设备访问
            if(preg_match('/iPhone/i',$_SERVER['HTTP_USER_AGENT'])){
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }else{
                
                echo '<title>温馨提示</title>';
                echo warnningInfo('仅限iOS设备访问');
            }
        }else if($dwz_type == 4){
            
            // 仅限Android设备访问
            if(preg_match('/Android/i',$_SERVER['HTTP_USER_AGENT'])){
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }else{
                
                echo '<title>温馨提示</title>';
                echo warnningInfo('仅限Android设备访问');
            }
        }else if($dwz_type == 5){
            
            // 仅限手机浏览器访问
            if(
                preg_match('/MicroMessenger/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/Windows/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/Macintosh/i',$_SERVER['HTTP_USER_AGENT']) || 
                preg_match('/X11/i',$_SERVER['HTTP_USER_AGENT'])){
                
                echo '<title>温馨提示</title>';
                echo warnningInfo('仅限手机浏览器访问');
            }else{
                
                // 301跳转
                header('HTTP/1.1 301 Moved Permanently');
            
                // 跳转
                header('Location:'.$dwz_url);
            }
        }else if($dwz_type == 6){
            
            // 通过获取屏幕分辨率宽度判断当前的设备
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
                
                echo '<title>温馨提示</title>';
                echo warnningInfo('仅限电脑访问');
            }
        }else{
            
            echo '<title>温馨提示</title>';
            echo warnningInfo('程序发生错误');
        }
    }

?>

</body>
</html>