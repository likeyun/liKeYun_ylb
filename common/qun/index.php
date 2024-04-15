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

    /**
     * 标题：群活码公共页面
     * 维护：2024年1月3日
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     * 该软件遵循MIT开源协议。
     */
     
    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数
    $qid = trim(intval($_GET['qid']));
    
    // 过滤参数
    if($qid){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取群活码信息
        $getQunInfoResult = $db->set_table('huoma_qun')->find(['qun_id' => $qid]);
        
        // 验证该群活码是否存在
        if($getQunInfoResult){
            
            // 解析所需字段
            $qun_title = getSqlData($getQunInfoResult,'qun_title');
            $qun_status = getSqlData($getQunInfoResult,'qun_status');
            $qun_qc = getSqlData($getQunInfoResult,'qun_qc');
            $qun_pv = getSqlData($getQunInfoResult,'qun_pv');
            $qun_kf = getSqlData($getQunInfoResult,'qun_kf');
            $qun_kf_status = getSqlData($getQunInfoResult,'qun_kf_status');
            $qun_safety = getSqlData($getQunInfoResult,'qun_safety');
            $qun_beizhu = getSqlData($getQunInfoResult,'qun_beizhu');
            $qun_notify = getSqlData($getQunInfoResult,'qun_notify');
            
            // 判断该群活码的状态
            if($qun_status == 1){
                
                // 当前状态：正常
                // --------------
                // 更新当前群活码的总访问量
                updateQunPv($db,$qid,$qun_pv);
                
                // 更新当前群活码的今天访问量
                updateQunTodayPv($db,$qid);
                
                // 更新当前小时的总访问量
                updateCurrentHourPageView($db,'qun');
                
                // 更新群活码今天ip总访问量
                updateTodayIpNum($db);
                
                // 显示符合阈值条件的二维码
                // 定义一个数组变量用于储存当前qun_id的所有二维码
                $QrcodeList = array();
                
                // 获取当前qun_id的所有二维码
                $getQrcodeListResult = $db->set_table('huoma_qun_zima')->findAll(['qun_id'=>$qid]);
                
                // 判断获取结果
                if($getQrcodeListResult){
                    
                    // 占位
                    // 顶部扫码安全提示固定定位导致的空缺，用这个占位补上
                    echo '<div id="zhanwei"></div>';

                    // 去重（qun_qc == 1才会执行以下代码）
                    if($qun_qc == 1){
                        
                        // 获取缓存
                        if ($_COOKIE[$qid] && !empty($_COOKIE[$qid])) {
                            
                            // 顶部三件套（标题、扫码安全验证提示、备注）
                            topMsg($qun_title,$qun_safety,'');
                            
                            // 把首次进入页面展示的二维码展示出来
                            // 7天内都是展示这个二维码
                            // 忽略阈值、忽略更新二维码
                            // 只要开启去重功能，你所进行的操作
                            // 均不会被老用户查看到，新扫码的用户因为没有缓存
                            // 所以新扫码的人是看到你最后操作的阈值条件去展示二维码
                            // 去重功能开启后，不计算扫码次数
                            if($qun_kf) {
                                
                                // 如果上传了客服二维码
                                // 优先展示客服二维码
                                $qc_Show = $qun_kf;
                            }else {
                               
                                // 否则使用缓存
                                $qc_Show = $_COOKIE[$qid];
                            }
                            
                            echo '
                                <p id="scanTips">请长按下方二维码进群</p>
                                <div id="zm_qrcode">
                                    <img src="'.$qc_Show.'" />
                                </div>';
                            exit;
                        }
                    }
                    
                    // 将当前qun_id的所有二维码存进上面定义的数组变量
                    $QrcodeList = $getQrcodeListResult;
                    
                    // 定义一个数组变量用于储存遍历过的二维码用于后期计数
                    $QrcodeForeachList = [];
                    
                    // 遍历数组
                    foreach ($QrcodeList as $k => $v){
                        
                        // 根据阈值条件遍历一个符合阈值条件的二维码
                        // （1）二维码的访问量 < 阈值
                        // （2）二维码的使用状态正常
                        if($QrcodeList[$k]['zm_pv'] < $QrcodeList[$k]['zm_yz'] && $QrcodeList[$k]['zm_status'] == 1){
                            
                            // 解析所需字段
                            $zm_id = $QrcodeList[$k]['zm_id'];
                            $zm_yz = $QrcodeList[$k]['zm_yz'];
                            $zm_pv = $QrcodeList[$k]['zm_pv'];
                            $zm_qrcode = $QrcodeList[$k]['zm_qrcode'];
                            
                            // 有符合遍历条件的结果
                            $foreachResult = true;
                            $QrcodeForeachList = $QrcodeList[$k];
                            
                            // 缓存（开启去重功能需要）
                            // 无论有没有开启去重功能
                            // 都会进行缓存，便于开启去重功能立马生效
                            if ($_COOKIE[$qid] == null) {
                                
                                // COOKIE有效期是7天
                                $expire_time = time()+60*60*24*7;
                                setcookie($qid, $zm_qrcode, $expire_time);
                            }
                            
                            // 顶部三件套（标题、扫码安全验证提示、备注）
                            topMsg($qun_title,$qun_safety,$qun_beizhu);
                            
                            // 展示符合阈值条件的群二维码
                            echo '
                            <p id="scanTips">请长按下方二维码进群</p>
                            <div id="zm_qrcode">
                                <img src="'.$zm_qrcode.'" />
                            </div>';
                            
                            // 客服
                            if($qun_kf_status == 1){
                                
                                // 显示一个超链接
                                echo '<div id="qun_kf"><a href="'.$qun_kf.'">联系客服</a></div>';
                                echo '<div style="width:100%;height:50px;"></div>';
                            }
                            
                            // 更新当前群二维码的访问量
                            // 注意：仅更新符合当前阈值条件的群二维码访问量
                            updateQunQrcodePv($db,$zm_id,$zm_pv);
                            
                            // 只需要获取符合当前阈值条件的第一个结果
                            // 所以循环一次就得跳出
                            exit;
                        }else{
                            
                            // 无符合遍历条件的结果
                            $foreachResult = false;
                        }
                    } // foreach ($QrcodeList as $k => $v)
                    
                    // 当遍历结果为false的时候或遍历后的数组<=0的时候
                    // 即不符合以上遍历条件的情况
                    // 简单来说就是没有二维码展示了
                    // 就只能展示下面这些
                    if($foreachResult == false || count($QrcodeForeachList) <= 0){
                        
                        // 暂无符合阈值条件的二维码
                        echo '<script>document.querySelector("#zhanwei").remove();</script>';
                        echo warnInfo('温馨提示','扫码次数已达上限');
                        
                        // 发送通知
                        if($qun_notify){
                            
                            // 请求推送
                            sendNotification($qun_notify,"群活码【".$qun_title."】达到阈值上限，请及时更新！",$db);
                        }
                        
                        // 这里直接展示客服二维码
                        // 因为是没有符合阈值条件的群二维码
                        // 所以直接展示客服二维码去联系客服了解详细的情况
                        // 一方面是让扫码用户及时了解到为什么会没有展示群二维码
                        // 如果客服发现是阈值爆表了那么客服可以立马登录后台设置新的阈值
                        // 另一方面是不放过任何引流的机会
                        if($qun_kf_status == 1){
                            
                            // 直接显示客服二维码
                            echo showKfQrcode($qun_kf);
                        }
                    }
                    
                }else{
                    
                    // 获取不到二维码
                    // 代表群活码上传之后，还没上传群二维码
                    echo warnInfo('温馨提示','管理员暂未上传群二维码');
                } // if($getQrcodeListResult)
            }else{
                
                // 当前状态：停用
                // qun_status !== 1的情况
                echo warnInfo('温馨提示','该群已被管理员暂停使用');
            } // if($qun_status == 1)
        }else{
            
            // 不存在
            // 获取不到该qun_id的详情
            echo warnInfo('温馨提示','该群不存在或已被管理员删除');
        } // if($getQunInfoResult)
        
    } // if($qid)
    
    
    /**
     * 封装的一些操作函数
    **/
    
    // 更新群活码今天ip总访问量
    function updateTodayIpNum($db){
        
        // 获取ip地址
        $getIP = $_SERVER['REMOTE_ADDR'];
        
        // 获取今天的ip记录数
        $getTodayIpNum = $db->set_table('huoma_ip')->find(['ip_create_time'=>date('Y-m-d')]);
        
        // 如果有记录
        if($getTodayIpNum){
            
            // 查询当前ip是否为今天首次访问
            $getThisIpISFirstTimeToday = $db->set_table('huoma_ip_temp')->find(['create_date'=>date('Y-m-d'),'ip'=>$getIP,'from_page'=>'qun']);
            
            // 如果没有记录
            // 说明这个ip是今天第一次访问
            if(!$getThisIpISFirstTimeToday){
                
                // 将当前ip添加至临时ip表
                $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'qun']);
                
                // 然后更新今天的ip记录数
                $qun_ip = json_decode(json_encode($getTodayIpNum))->qun_ip;
                $newQun_ip = $qun_ip + 1;
                $db->set_table('huoma_ip')->update(['ip_create_time'=>date('Y-m-d')],['qun_ip'=>$newQun_ip]);
            }
        }else{
            
            // 如果没有记录
            // 将当前ip添加至临时ip表并记录为今天的ip访问
            $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'qun']);
            
            // 新增这个ip今天的访问次数
            $db->set_table('huoma_ip')->add(['qun_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
        }
        
        // 昨天的日期
        $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
        
        // 检查是否存在昨天的ip记录
        $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(['create_date'=>$yesterdayDate,'from_page'=>'qun']);
        
        // 如果有记录
        if($getYesterdayIp){
            
            // 清理昨天日期的临时ip
            $db->set_table('huoma_ip_temp')->delete(['create_date'=>$yesterdayDate,'from_page'=>'qun']);
        }
    }
    
    // 更新当前群活码的今天访问量
    function updateQunTodayPv($db,$qid){
        
        // 获取qun_today_pv字段并提取pv和date
        $getTodayQunPv = $db->set_table('huoma_qun')->find(['qun_id'=>$qid]);
        if($getTodayQunPv){
            
            // qun_today_pv的值
            $qun_today_pv = getSqlData($getTodayQunPv,'qun_today_pv');
            
            // pv的值
            $today_pv = json_decode($qun_today_pv,true)['pv'];
            
            // date的值
            $today_date = json_decode($qun_today_pv,true)['date'];
            
            // 检查这个记录是不是今天的
            if($today_date == date('Y-m-d')){
                
                // 如果是今天的
                // 更新pv的值
                $newToday_pv = $today_pv + 1;
                $db->set_table('huoma_qun')->update(
                    ['qun_id'=>$qid],
                    ['qun_today_pv'=>'{"pv":"'.$newToday_pv.'","date":"'.date('Y-m-d').'"}']
                );
            }else{
                
                // 如果不是今天的
                // 先将日期更新为今天的
                // 再更新今天pv的值
                $db->set_table('huoma_qun')->update(
                    ['qun_id'=>$qid],
                    ['qun_today_pv'=>'{"pv":"1","date":"'.date('Y-m-d').'"}']
                );
            }
        }
    }
    
    // 更新当前小时的总访问量
    function updateCurrentHourPageView($db,$hourNum_type){
        
        // 引入公共文件
        include '../../console/public/updateCurrentHourPageView.php';
    }
    
    // 更新当前群活码的总访问量
    function updateQunPv($db,$qid,$qun_pv){
        
        // 即qid的访问量
        $newQun_pv = $qun_pv + 1;
        $db->set_table('huoma_qun')->update(
            ['qun_id'=>$qid],
            ['qun_pv'=>$newQun_pv]
        );
    }
    
    // 更新当前展示的群二维码的访问量
    function updateQunQrcodePv($db,$zm_id,$zm_pv){
        
        // 即zm_id的访问量
        $newQunQrcode_pv = $zm_pv + 1;
        $db->set_table('huoma_qun_zima')->update(
            ['zm_id'=>$zm_id],
            ['zm_pv'=>$newQunQrcode_pv]
        );
    }
    
    // 发送通知
    function sendNotification($noti_type,$noti_text,$db){
        
        // 根据noti_type选择发送的渠道
        include_once '../../console/public/sendNotification.php';
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
            <img src="../../static/img/warn.png" />
        </div>
        <p id="warnText">'.$warnText.'</p>';
    }
    
    // 直接显示客服二维码
    function showKfQrcode($qun_kf){
        
        // 传入客服二维码IMGURL
        return '
        <div id="showKfQrcode">
            <img src="'.$qun_kf.'" />
        </div>
        <p id="warnningText">可咨询微信客服了解</p>';
        
    }
    
    // 顶部三件套（标题、扫码安全验证提示、备注）
    function topMsg($qun_title,$qun_safety,$qun_beizhu){
        
        // 标题
        echo '<title>'.$qun_title.'</title>';
        
        // 顶部扫码安全提示
        if($qun_safety == 1){
            
            // 开启
            echo '
            <div id="qun_safety">
                <div class="icon"></div>
                <div class="text">二维码已通过安全验证</div>
            </div>';
        }
        
        // 备注
        if(!empty($qun_beizhu)){
            
            // 显示备注内容
            echo '<div id="qun_beizhu">'.$qun_beizhu.'</div>';
        }
    }

?>

</body>
</html>