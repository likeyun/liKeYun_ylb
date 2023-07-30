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
        <script src="../../static/js/jquery.min.js"></script>
    </head>
    
    <body style="background:#fff;">
        
    <?php

        /**
         * 标题：分享卡片公共页面
         * 维护：2023年7月31日
         * 作者：TANKING
         * 博客：https://segmentfault.com/u/tanking
         * 摘要：优化代码结构、优化错误提示
         */
    
        // 页面编码
        header("Content-type:text/html;charset=utf-8");
        
        // 获取参数
        $sid = trim(intval($_GET['sid']));
        
        // 过滤参数
        if($sid && $sid !== ''){
            
            // 数据库配置
            include '../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);
            
            // 获取分享卡
            $getshareCardInfoResult = $db->set_table('huoma_shareCard')->find(['shareCard_id'=>$sid]);
            
            if($getshareCardInfoResult){
                
                // 目标链接
                $shareCard_url = getSqlData($getshareCardInfoResult,'shareCard_url');
                
                // 状态
                $shareCard_status = getSqlData($getshareCardInfoResult,'shareCard_status');
                
                if($shareCard_status == 1){
                    
                    // 更新当前分享卡片的访问量
                    updateThisshareCardPv($db,$sid);
                    
                    // 记录今天ip访问量
                    updateTodayIpNum($db);
                    
                    // 更新当前小时的总访问量
                    updateCurrentHourPageView($db,'shareCard');
                
                    // 跳转
                    header('Location:'.$shareCard_url);
                }else{
                    
                    echo '<title>温馨提示</title>';
                    echo warnningInfo('该链接已被管理员暂停使用');
                }
                
            }else{
                
                // 不存在
                // 获取不到该shareCard_id的详情
                echo '<title>温馨提示</title>';
                echo warnningInfo('该链接不存在或已被管理员删除');
            } // if($getshareCardInfoResult && $getshareCardInfoResult > 0){
            
        } // if($sid && $sid !== '')
        
        /**
         * 以下是封装的一些操作函数
         * 一方面是便于多处调用
         * 另一方面是保持代码的整洁可读性
         */
        
        // 更新当前中间页的访问量
        function updateThisshareCardPv($db,$sid){
            
            // 传入sid
            $updateThisshareCardPv = 'UPDATE huoma_shareCard SET shareCard_pv=shareCard_pv+1 WHERE shareCard_id="'.$sid.'"';
            $db->set_table('huoma_shareCard')->findSql($updateThisshareCardPv);
        }
        
        // 更新当前小时的总访问量
        function updateCurrentHourPageView($db,$hourNum_type){
            
            // 引入公共文件
            include '../../console/public/updateCurrentHourPageView.php';
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
        
        // 解析数组
        function getSqlData($result,$field){
            
            // 传入数组和需要解析的字段
            return json_decode(json_encode($result))->$field;
        }
        
        // 提醒文字
        function warnningInfo($warnningText){
            
            // 传入warnningText
            return '<div id="warnning"><img src="../../static/img/warnning.svg" /></div><p id="warnningText">'.$warnningText.'</p>';
            
        }

    ?>
    
    </body>
</html>