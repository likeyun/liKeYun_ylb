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
    $kid = trim(intval($_GET['kid']));
    
    // 过滤参数
    if($kid && $kid !== ''){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取客服码信息
        $getKfInfo = ['kf_id'=>$kid];
        $getKfInfoResult = $db->set_table('huoma_kf')->find($getKfInfo);
        
        // 验证该客服码是否存在
        if($getKfInfoResult && $getKfInfoResult > 0){
            
            // 存在
            // 解析所需字段
            $kf_title = getSqlData($getKfInfoResult,'kf_title');
            $kf_status = getSqlData($getKfInfoResult,'kf_status');
            $kf_model = getSqlData($getKfInfoResult,'kf_model');
            $kf_online = getSqlData($getKfInfoResult,'kf_online');
            $kf_safety = getSqlData($getKfInfoResult,'kf_safety');
            $kf_beizhu = getSqlData($getKfInfoResult,'kf_beizhu');
            
            // 判断该客服活码的状态
            if($kf_status == 1){
                
                // 当前状态：正常
                // 更新当前客服活码的访问量
                updateThisKfHmPv($db,$kid);
                
                // 更新数据统计表（首页展示各时段数据）
                updateCountChartPv($db);

                // 占位（顶部扫码安全提示固定定位导致的空缺，用这个占位补上）
                echo '<div id="zhanwei"></div>';
                
                // 定义一个数组变量用于储存当前kf_id的所有二维码
                $QrcodeList = array();
                
                // 获取当前kf_id的所有二维码
                $getQrcodeList = ['kf_id'=>$kid];
                $getQrcodeListResult = $db->set_table('huoma_kf_zima')->findAll($getQrcodeList);
                
                // 判断获取结果
                if($getQrcodeListResult && $getQrcodeListResult > 0){

                    // 将当前kf_id的所有二维码存进上面定义的数组变量
                    $QrcodeList = $getQrcodeListResult;
                    
                    // 定义一个数组变量用于储存遍历过的二维码用于后期计数
                    $QrcodeForeachList = [];
                    
                    // 循环模式
                    if($kf_model == 1){
                        
                        // 阈值模式 // 阈值模式 // 阈值模式 //
                        // 阈值模式 // 阈值模式 // 阈值模式 //
                        // 阈值模式 // 阈值模式 // 阈值模式 //
                        
                        // 显示符合阈值条件的二维码
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
                                $zm_num = $QrcodeList[$k]['zm_num'];
                                
                                // 有符合遍历条件的结果
                                $foreachResult = true;
                                $QrcodeForeachList = $QrcodeList[$k];
    
                                // 顶部三件套（标题、扫码安全验证提示、备注）
                                topMsg($kf_title,$kf_safety,$kf_beizhu);
                                
                                // 展示符合阈值条件的客服二维码
                                echo '<p id="scanTips">请长按下方二维码联系客服</p><div id="kfzm_qrcode"><img src="'.$zm_qrcode.'" /></div>';
                                
                                // 微信号
                                echo '<p id="wxnum">微信号：'.$zm_num.'</p>';
                                
                                // 在线状态
                                if($kf_online == 1){
                                    
                                    // 这里后面还得做判断去显示在线还是不在线
                                    // echo kfonlineStatus(false,'当前客服不在线，可能回复较慢！');
                                    echo kfonlineStatus(true,'当前客服在线，可随时联系！');
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
                        }
                        
                    }else{
                        
                        // 随机模式 // 随机模式 随机模式 //
                        // 随机模式 // 随机模式 随机模式 //
                        // 随机模式 // 随机模式 随机模式 //
                        
                        // 将当前kf_id的所有二维码的数组成员进行打乱
                        // 因为每次打乱的顺序一样，所以只需要取第一个遍历的结果
                        shuffle($QrcodeList);

                        // 遍历数组
                        foreach ($QrcodeList as $k => $v){
                            
                            // 解析所需字段
                            $zm_id = $QrcodeList[$k]['zm_id'];
                            $zm_yz = $QrcodeList[$k]['zm_yz'];
                            $zm_pv = $QrcodeList[$k]['zm_pv'];
                            $zm_qrcode = $QrcodeList[$k]['zm_qrcode'];
                            $zm_num = $QrcodeList[$k]['zm_num'];

                            // 顶部三件套（标题、扫码安全验证提示、备注）
                            topMsg($kf_title,$kf_safety,$kf_beizhu);
                            
                            // 展示遍历的第一个客服二维码
                            echo '<p id="scanTips">请长按下方二维码联系客服</p><div id="kfzm_qrcode"><img src="'.$zm_qrcode.'" /></div>';
                            
                            // 微信号
                            if(!empty($zm_num)){
                                echo '<p id="wxnum">微信号：'.$zm_num.'</p>';
                            }
                            
                            // 在线状态
                            if($kf_online == 1){
                                
                                // 在线时间配置
                                // 如需修改配置请拉到底部找到【在线时间配置】函数 onlineConfig
                                onlineConfig();
                            }

                            // 更新当前二维码的访问量（仅更新符合当前阈值条件的二维码的访问量）
                            updateThisQrcodePv($db,$zm_id);
                            
                            // 只需要遍历的第一个客服二维码
                            // 所以循环一次就得跳出
                            exit;
                        } // foreach ($QrcodeList as $k => $v)
                    }
                    
                }else{
                    
                    // 获取不到二维码
                    // 代表客服码创建之后，还没上传客服二维码
                    echo '<title>温馨提示</title>';
                    echo warnningInfo('管理员暂未上传客服二维码');
                } // if($getQrcodeListResult && $getQrcodeListResult > 0)
                
            }else{
                
                // 当前状态：停用
                // kf_status !== 1的情况
                echo '<title>温馨提示</title>';
                echo warnningInfo('二维码已被管理员暂停使用');
            } // if($kf_status == 1)
        }else{
            
            // 不存在
            // 获取不到该kf_id的详情
            echo '<title>温馨提示</title>';
            echo warnningInfo('二维码不存在或已被管理员删除');
        } // if($getKfInfoResult && $getKfInfoResult > 0)
        
    } // if($kid && $kid !== '')
    
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
        $updatePv = 'UPDATE huoma_count SET count_kf_pv=count_kf_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // （1）将日期更新为今天并且访问量归零
    // （2）更新当前小时的访问量
    function updateDefault($huoma_count){
        
        $thisDate = date('Y-m-d');
        $updateDefault = 'UPDATE huoma_count SET count_qun_pv="0",count_kf_pv="0",count_channel_pv="0",count_dwz_pv="0",count_zjy_pv="0",count_date="'.$thisDate.'"';
        $huoma_count->findSql($updateDefault);
        $thisHour = date('H');
        $updatePv = 'UPDATE huoma_count SET count_kf_pv=count_kf_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // 更新当前客服码的访问量
    function updateThisKfHmPv($db,$kid){
        
        // 传入kf_id
        $updateThisKfHmPv = 'UPDATE huoma_kf SET kf_pv=kf_pv+1 WHERE kf_id="'.$kid.'"';
        $db->set_table('huoma_kf')->findSql($updateThisKfHmPv);
    }
    
    // 更新当前二维码的访问量
    function updateThisQrcodePv($db,$zm_id){
        
        // 传入zm_id
        $updateThisQrcodePv = 'UPDATE huoma_kf_zima SET zm_pv=zm_pv+1 WHERE zm_id="'.$zm_id.'"';
        $db->set_table('huoma_kf_zima')->findSql($updateThisQrcodePv);
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
    
    // 顶部三件套（标题、扫码安全验证提示、备注）
    function topMsg($kf_title,$kf_safety,$kf_beizhu){
        
        // 标题
        echo '<title>'.$kf_title.'</title>';
        
        // 顶部扫码安全提示
        if($kf_safety == 1){
            
            // 开启
            echo '<div id="kf_safety"><div class="icon"></div><div class="text">二维码已通过安全验证</div></div>';
        }
        
        // 备注
        if(!empty($kf_beizhu)){
            
            // 显示备注内容
            echo '<div id="kf_beizhu">'.$kf_beizhu.'</div>';
        }
    }
    
    // 在线时间配置
    function onlineConfig(){
        
        // 根据自己的在线时间去修改
        // 北京时间
        date_default_timezone_set('asia/shanghai');
        
        // 获取周六还是周日
		$week = date('w');
		
	    // 获取当前时间并转换为unix时间戳
		$time = strtotime(date('H:i'));
		
	    // 开始筛选
	    if($week == 6){
	        
	        // $week == 6 代表星期六
	        // 周六不在线
	        echo kfonlineStatus(false,'周六客服不在线，可能回复较慢！');
	        
	        // 周六在线
	        // echo kfonlineStatus(true,'当前客服在线，可随时联系！');
	        
	    }else if($week == 0){
	        
            // $week == 0 代表星期日
	        // 周日不在线
	        echo kfonlineStatus(false,'周日客服不在线，可能回复较慢！');
	        
	        // 周日在线
	        // echo kfonlineStatus(true,'当前客服在线，可随时联系！');
	        
	    }else if(
	        
	        // 上午9:00 - 12:00在线
            // 下午14:00 - 18:00在线
            // 晚上20:00 - 22:00在线
	        $time >= strtotime('9:00') && $time <= strtotime('12:00') || 
	        $time >= strtotime('14:00') && $time <= strtotime('18:30') || 
	        $time >= strtotime('20:00') && $time <= strtotime('22:00')){
	            
	            // 符合以上三个时间段的都显示在线
	            // 可按照自己的时间去调整以上数据
	            echo kfonlineStatus(true,'当前客服在线，可随时联系！');
	            
	        }else{
	        
	        // 其它时间均不在线
	        echo kfonlineStatus(false,'当前客服不在线，可能回复较慢！');
	    }
    }
    
    // 客服在线状态
    function kfonlineStatus($status,$text){
        
        // 传入布尔类型
        if($status == true){
            
            // 在线
            return '<div id="kf_online_true">'.$text.'</div>';
        }else{
            
            // 不在线
            return '<div id="kf_online_false">'.$text.'</div>';
        }
    }

?>

</body>
</html>