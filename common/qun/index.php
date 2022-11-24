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
    
    // 获取参数（intval函数用于过滤特殊字符防止SQL注入）
    $qid = trim(intval($_GET['qid']));
    
    // 过滤参数
    if($qid && $qid !== ''){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取群活码信息
        $getQunInfo = ['qun_id'=>$qid];
        $getQunInfoResult = $db->set_table('huoma_qun')->find($getQunInfo);
        
        // 验证该群活码是否存在
        if($getQunInfoResult && $getQunInfoResult > 0){
            
            // 存在
            // 解析所需字段
            $qun_title = getSqlData($getQunInfoResult,'qun_title');
            $qun_status = getSqlData($getQunInfoResult,'qun_status');
            $qun_qc = getSqlData($getQunInfoResult,'qun_qc');
            $qun_kf = getSqlData($getQunInfoResult,'qun_kf');
            $qun_kf_status = getSqlData($getQunInfoResult,'qun_kf_status');
            $qun_safety = getSqlData($getQunInfoResult,'qun_safety');
            $qun_beizhu = getSqlData($getQunInfoResult,'qun_beizhu');
            
            // 判断该群活码的状态
            if($qun_status == 1){
                
                // 当前状态：正常
                // 更新当前群活码的访问量
                updateThisQunHmPv($db,$qid);
                
                // 更新数据统计表（首页展示各时段数据）
                updateCountChartPv($db);

                // 占位（顶部扫码安全提示固定定位导致的空缺，用这个占位补上）
                echo '<div id="zhanwei"></div>';
                
                // 显示符合阈值条件的二维码
                // 定义一个数组变量用于储存当前qun_id的所有二维码
                $QrcodeList = array();
                
                // 获取当前qun_id的所有二维码
                $getQrcodeList = ['qun_id'=>$qid];
                $getQrcodeListResult = $db->set_table('huoma_qun_zima')->findAll($getQrcodeList);
                
                // 判断获取结果
                if($getQrcodeListResult && $getQrcodeListResult > 0){

                    // 去重（qun_qc == 1才会执行以下代码）
                    if($qun_qc == 1){
                        
                        // 获取缓存
                        if ($_COOKIE[$qid] && !empty($_COOKIE[$qid])) {
                            
                            // 顶部三件套（标题、扫码安全验证提示、备注）
                            topMsg($qun_title,$qun_safety,$qun_beizhu);
                            
                            // 把首次进入页面展示的二维码展示出来
                            // 7天内都是展示这个二维码
                            // 忽略阈值、忽略更新二维码
                            // 只要开启去重功能，你所进行的操作
                            // 均不会被老用户查看到，新扫码的用户因为没有缓存
                            // 所以新扫码的人是看到你最后操作的阈值条件去展示二维码
                            // 去重功能开启后，不计算扫码次数
                            echo '<p id="scanTips">请长按下方二维码进群</p><div id="zm_qrcode"><img src="'.$_COOKIE[$qid].'" /></div>';
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
                            echo '<p id="scanTips">请长按下方二维码进群</p><div id="zm_qrcode"><img src="'.$zm_qrcode.'" /></div>';
                            
                            // 客服（qun_kf_status == 1才会显示客服）
                            if($qun_kf_status == 1){
                                
                                // 显示一个超链接
                                echo '<div id="qun_kf"><a href="'.$qun_kf.'">联系客服</a></div>';
                                echo '<div style="width:100%;height:50px;"></div>';
                            }
                            
                            // 更新当前二维码的访问量（仅更新符合当前阈值条件的二维码的访问量）
                            updateThisQrcodePv($db,$zm_id);
                            
                            // 只需要获取符合当前阈值条件的第一个结果
                            // 所以循环一次就得跳出
                            exit;
                        }else{
                            
                            // 无符合遍历条件的结果
                            $foreachResult = false;
                        }
                    } // foreach ($QrcodeList as $k => $v)
                    
                    // 当遍历结果为false的时候或者是遍历后的数组<=0的时候
                    // 简单来说就是不符合以上遍历条件的情况需要显示的内容
                    if($foreachResult == false || count($QrcodeForeachList) <= 0){
                        
                        // 暂无符合阈值条件的二维码
                        echo '<title>温馨提示</title>';
                        echo warnningInfo('扫码次数已达上限（阈值）');
                        
                        // 客服（qun_kf_status == 1才会显示客服）
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
                    echo '<title>温馨提示</title>';
                    echo warnningInfo('管理员暂未上传群二维码');
                } // if($getQrcodeListResult && $getQrcodeListResult > 0)
            }else{
                
                // 当前状态：停用
                // qun_status !== 1的情况
                echo '<title>温馨提示</title>';
                echo warnningInfo('该群已被管理员暂停使用');
            } // if($qun_status == 1)
        }else{
            
            // 不存在
            // 获取不到该qun_id的详情
            echo '<title>温馨提示</title>';
            echo warnningInfo('该群不存在或已被管理员删除');
        } // if($getQunInfoResult && $getQunInfoResult > 0)
        
    } // if($qid && $qid !== '')
    
    /**
     * 以下是封装的一些操作函数
     * 一方面是便于多处调用
     * 另一方面是保持代码的整洁可读性
    **/
     
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
        $updatePv = 'UPDATE huoma_count SET count_qun_pv=count_qun_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // （1）将日期更新为今天并且访问量归零
    // （2）更新当前小时的访问量
    function updateDefault($huoma_count){
        
        $thisDate = date('Y-m-d');
        $updateDefault = 'UPDATE huoma_count SET count_qun_pv="0",count_kf_pv="0",count_channel_pv="0",count_dwz_pv="0",count_zjy_pv="0",count_date="'.$thisDate.'"';
        $huoma_count->findSql($updateDefault);
        $thisHour = date('H');
        $updatePv = 'UPDATE huoma_count SET count_qun_pv=count_qun_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // 更新当前群活码的访问量
    function updateThisQunHmPv($db,$qid){
        
        // 传入qun_id
        $updateThisQunHmPv = 'UPDATE huoma_qun SET qun_pv=qun_pv+1 WHERE qun_id="'.$qid.'"';
        $db->set_table('huoma_qun')->findSql($updateThisQunHmPv);
    }
    
    // 更新当前二维码的访问量
    function updateThisQrcodePv($db,$zm_id){
        
        // 传入zm_id
        $updateThisQrcodePv = 'UPDATE huoma_qun_zima SET zm_pv=zm_pv+1 WHERE zm_id="'.$zm_id.'"';
        $db->set_table('huoma_qun_zima')->findSql($updateThisQrcodePv);
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
    
    // 直接显示客服二维码
    function showKfQrcode($qun_kf){
        
        // 传入客服二维码IMGURL
        return '<div id="showKfQrcode"><img src="'.$qun_kf.'" /></div><p id="warnningText">可咨询微信客服了解</p>';
        
    }
    
    // 顶部三件套（标题、扫码安全验证提示、备注）
    function topMsg($qun_title,$qun_safety,$qun_beizhu){
        
        // 标题
        echo '<title>'.$qun_title.'</title>';
        
        // 顶部扫码安全提示
        if($qun_safety == 1){
            
            // 开启
            echo '<div id="qun_safety"><div class="icon"></div><div class="text">二维码已通过安全验证</div></div>';
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