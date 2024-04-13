<link rel="stylesheet" href="../../../static/css/common.css">
<?php

    // 获取ID
    $sid = trim(intval($_GET['sid']));
    
    if($sid) {
        
        // 数据库配置
        include '../../../console/Db.php';
        $db = new DB_API($config);
    
        // 获取详情
        $getCardInfo = $db->set_table('huoma_shareCard')->find(['shareCard_id' => $sid]);
        
        if($getCardInfo) {
            
            // 标题
            $shareCard_title = $getCardInfo['shareCard_title'];
            
            // 描述
            $shareCard_desc = $getCardInfo['shareCard_desc'];
            
            // 图标
            $shareCard_img = $getCardInfo['shareCard_img'];
            
            // 目标链接
            $shareCard_url = $getCardInfo['shareCard_url'];
            
            // 状态
            $shareCard_status = $getCardInfo['shareCard_status'];
            
            if($shareCard_status == 2) {
                
                // 停用
                echo warnInfo('温馨提示','该链接已被管理员暂停使用!');
                exit;
            }else {
                
                // 更新当前分享卡片的访问量
                updateThisshareCardPv($db,$sid);
                
                // 记录今天ip访问量
                updateTodayIpNum($db);
                
                // 更新当前小时的总访问量
                updateCurrentHourPageView($db,'shareCard');
            }
        }else {
            
            echo warnInfo('温馨提示','该链接不存在或已被管理员删除!');
            exit;
        }
    }else {
        
        echo warnInfo('温馨提示','参数缺失!');
        exit;
    }
    
    /**
     * 以下是封装的一些操作函数
     * 一方面是便于多处调用
     * 另一方面是保持代码的整洁可读性
     */
    
    // 更新当前页的访问量
    function updateThisshareCardPv($db,$sid){
        
        // 传入sid
        $updateThisshareCardPv = 'UPDATE huoma_shareCard SET shareCard_pv=shareCard_pv+1 WHERE shareCard_id="'.$sid.'"';
        $db->set_table('huoma_shareCard')->findSql($updateThisshareCardPv);
    }
    
    // 更新当前小时的总访问量
    function updateCurrentHourPageView($db,$hourNum_type){
        
        // 引入公共文件
        include '../../../console/public/updateCurrentHourPageView.php';
    }
    
    // 解析数组
    function getSqlData($result,$field){
        
        // 传入数组和需要解析的字段
        return json_decode(json_encode($result))->$field;
    }
    
    // 记录今天ip访问量
    function updateTodayIpNum($db){
        
        // 获取ip地址
        $getIP = $_SERVER['REMOTE_ADDR'];
        
        // 获取今天的ip记录数
        $getTodayIpNum = $db->set_table('huoma_ip')->find(['ip_create_time'=>date('Y-m-d')]);
        
        // 如果有记录
        if($getTodayIpNum){
            
            // 查询当前ip是否为今天首次访问
            $getThisIpISFirstTimeToday = $db->set_table('huoma_ip_temp')->find(
                [
                    'create_date'=>date('Y-m-d'),
                    'ip'=>$getIP,
                    'from_page'=>'shareCard'
                ]
            );
            
            // 如果没有记录
            // 说明这个ip是今天第一次访问
            if(!$getThisIpISFirstTimeToday){
                
                // 将当前ip添加至临时ip表
                $db->set_table('huoma_ip_temp')->add(
                    [
                        'ip'=>$getIP,
                        'create_date'=>date('Y-m-d'),
                        'from_page'=>'shareCard'
                    ]
                );
                
                // 然后更新今天的ip记录数
                $shareCard_ip = json_decode(json_encode($getTodayIpNum))->shareCard_ip;
                $newShareCard_ip = $shareCard_ip + 1;
                $db->set_table('huoma_ip')->update(
                    ['ip_create_time'=>date('Y-m-d')],
                    ['shareCard_ip'=>$newShareCard_ip]
                );
            }
        }else{
            
            // 如果没有记录
            // 将当前ip添加至临时ip表并记录为今天的ip访问
            $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'shareCard']);
            
            // 新增这个ip今天的访问次数
            $db->set_table('huoma_ip')->add(['shareCard_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
        }
        
        // 昨天的日期
        $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
        
        // 检查是否存在昨天的ip记录
        $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(['create_date'=>$yesterdayDate,'from_page'=>'shareCard']);
        
        // 如果有记录
        if($getYesterdayIp){
            
            // 清理昨天日期的临时ip
            $db->set_table('huoma_ip_temp')->delete(['create_date'=>$yesterdayDate,'from_page'=>'shareCard']);
        }
    }
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="renderer" content="webkit">
        <meta name="layoutmode" content="standard">
        <meta name="imagemode" content="force">
        <meta name="wap-font-scale" content="no">
        <meta name="format-detection" content="telephone=no">
        <title>Safari生成分享卡片</title>
        <meta property="og:type" content="website">
        <meta property="og:title" content="<?php echo $shareCard_title; ?>">
        <meta property="og:description" content="<?php echo $shareCard_desc; ?>">
        <meta property="og:image" content="<?php echo $shareCard_img; ?>">
        <link rel="shortcut icon" href="../../../static/img/safari-fav.png">
    </head>
    
    <body>
        
        <?php
            
            if(getBrowserType() == 'WeChat') {
                
                // 微信
                // 跳转到目标链接
                echo '<title>正在跳转...</title>';
                header('Location:' . $shareCard_url);
                
            }else if(getBrowserType() == 'Safari') {
                
                // Safari
                echo '<img src="../../../static/img/share_yindao.jpg" style="width:100%;" />';
            }else {
                
                // 其他
                echo warnInfo('温馨提示','当前浏览器不支持!');
            }
            
            // 获取打开当前页面的浏览器
            function getBrowserType() {
                
                // 获取HTTP_USER_AGENT
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
            
                // 检测是否在微信内
                if (strpos($userAgent, 'MicroMessenger') !== false) {
                    return 'WeChat';
                }
            
                // 检测是否是Safari
                if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
                    return 'Safari';
                }
            
                return 'Unknown';
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