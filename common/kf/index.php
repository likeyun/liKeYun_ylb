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
<script type="text/javascript">

    // 设置剪贴板
    function copyWeChatNum(e) {
    
        // 创建一个临时textarea元素
        const tempTextArea = document.createElement('textarea');
    
        // 使其在视觉上不可见
        tempTextArea.value = e.dataset.wxnum;
        tempTextArea.style.position = 'fixed';
        document.body.appendChild(tempTextArea);
    
        // 选择并复制文本
        tempTextArea.select();
        document.execCommand('copy');
    
        // 清理并移除临时元素
        document.body.removeChild(tempTextArea);
    
        // 复制成功
        document.querySelector('#wxnum .copy').innerText = '已复制';
        
        // 恢复
        setTimeout(function() {
        
            // 恢复为复制文案
            document.querySelector('#wxnum .copy').innerText = '复制';
        }, 3000);
    }

</script>
<?php

    /**
     * 标题：客服码公共页面
     * 维护：2024-08-19
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     */
     
    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数
    $kid = trim(intval($_GET['kid']));
    
    // 过滤参数
    if($kid){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取客服码信息
        $getKfInfo = ['kf_id'=>$kid];
        $getKfInfoResult = $db->set_table('huoma_kf')->find($getKfInfo);
        
        // 验证该客服码是否存在
        if($getKfInfoResult && $getKfInfoResult > 0){
            
            // 解析所需字段
            $kf_title = getSqlData($getKfInfoResult,'kf_title'); // 客服码标题
            $kf_pv = getSqlData($getKfInfoResult,'kf_pv'); // 总访问量
            $kf_status = getSqlData($getKfInfoResult,'kf_status'); // 客服码状态 1开 2关
            $kf_model = getSqlData($getKfInfoResult,'kf_model'); // 展示模式 1阈值 2随机
            $kf_online = getSqlData($getKfInfoResult,'kf_online'); // 在线状态 1在线 2不在线
            $kf_safety = getSqlData($getKfInfoResult,'kf_safety'); // 顶部安全提示 1显示 2隐藏
            $kf_beizhu = getSqlData($getKfInfoResult,'kf_beizhu'); // 文案
            $kf_onlinetimes = getSqlData($getKfInfoResult,'kf_onlinetimes'); // 在线时间配置
            $kf_qc = getSqlData($getKfInfoResult,'kf_qc'); // 定制功能，去重
            
            // 客服活码的状态
            if($kf_status == 1){
                
                // 当前状态：正常
                // 更新当前客服码的总访问量
                updateKfPv($db,$kid,$kf_pv);
                
                // 更新当前客服码的今天访问量
                updateTodayPv($db,$kid);
                
                // 更新当前小时的总访问量
                updateCurrentHourPageView($db,'kf');
                
                // 记录今天ip访问量
                updateTodayIpNum($db);
                
                // 定义一个数组变量用于储存当前kf_id的所有二维码
                $QrcodeList = array();
                
                // 获取当前kf_id的所有二维码
                $getQrcodeListResult = $db->set_table('huoma_kf_zima')->findAll(['kf_id'=>$kid]);
                
                // 获取结果
                if($getQrcodeListResult && $getQrcodeListResult > 0){
                    
                    // 占位
                    // 顶部扫码安全提示固定定位导致的空缺
                    // 用这个占位补上
                    echo '<div id="zhanwei"></div>';

                    // 将当前kf_id的所有二维码
                    // 存进上面定义的$QrcodeList数组变量
                    $QrcodeList = $getQrcodeListResult;
                    
                    // 定义一个数组变量
                    // 用于储存遍历过的二维码用于后期计数
                    $QrcodeForeachList = [];
                    
                    // 展示模式
                    if($kf_model == 1){
                        
                        // 阈值模式
                        // 阈值模式
                        // 阈值模式
                        
                        // 显示符合阈值条件的二维码
                        foreach ($QrcodeList as $k => $v){
                            
                            // 根据阈值条件遍历一个符合阈值条件的二维码
                            // 条件如下：
                            // （1）二维码的访问量 < 阈值
                            // （2）二维码的使用状态正常
                            if($QrcodeList[$k]['zm_pv'] < $QrcodeList[$k]['zm_yz'] && $QrcodeList[$k]['zm_status'] == 1){
                                
                                // 解析所需字段
                                $zm_id = $QrcodeList[$k]['zm_id']; // 微信二维码id
                                $zm_yz = $QrcodeList[$k]['zm_yz']; // 微信二维码阈值
                                $zm_pv = $QrcodeList[$k]['zm_pv']; // 微信二维码访问量
                                $zm_qrcode = $QrcodeList[$k]['zm_qrcode']; // 微信二维码图片
                                $zm_num = $QrcodeList[$k]['zm_num']; // 微信二维码的微信号
                                
                                // 有符合遍历条件的结果
                                $foreachResult = true;
                                $QrcodeForeachList = $QrcodeList[$k];
    
                                // 顶部三件套（标题、扫码安全验证提示显隐状态、备注）
                                topMsg($kf_title,$kf_safety,$kf_beizhu);
                                
                                // 去重
                                if($kf_qc == 1) {
                                    
                                    // 读取cookie
                                    // 渲染当前设备第一次扫码展示的存储到cookie的子码
                                    if ($_COOKIE[$kid] && !empty($_COOKIE[$kid])) {
                                        
                                        echo '
                                        <p id="scanTips">请长按下方二维码添加微信</p>
                                        <div id="zm_qrcode" class="qrcode-view">
                                            <img src="'.$_COOKIE[$kid].'" />
                                        </div>';
                                        exit;
                                    }
                                }else {
                                    
                                    // 未开启去重
                                    // 渲染当前随机的子码
                                    echo '
                                    <p id="scanTips">请长按识别二维码添加微信</p>
                                    <div id="zm_qrcode" class="qrcode-view">
                                        <img src="'.$zm_qrcode.'" />
                                    </div>';
                                }
                                
                                // 微信号
                                echo 
                                '<p id="wxnum">
                                    <span class="num">微信号 : '.$zm_num.'</span>
                                    <span class="copy" data-wxnum="'.$zm_num.'" onclick="copyWeChatNum(this)">复制</span>
                                </p>';
                                
                                // 在线状态
                                if($kf_online == 1){
                                    
                                    // 开启
                                    showOnlineStatus($kf_onlinetimes);
                                }
    
                                // 更新当前微信二维码的访问量
                                // 仅更新符合当前阈值条件的zm_id的微信二维码的访问量
                                updateKfQrcodePv($db,$zm_id,$zm_pv);
                                
                                // 将当前子码存储到cookie
                                if ($_COOKIE[$kid] == null) {

                                    $cookie_expire = time() + (30 * 24 * 60 * 60); // 30天
                                    setcookie($kid, $zm_qrcode, $cookie_expire);
                                }
                                
                                // 只需要获取符合当前阈值条件的第一个结果
                                // 所以循环一次就得跳出去
                                // 所以用了exit
                                exit;
                            }else{
                                
                                // 无符合遍历条件的结果
                                $foreachResult = false;
                            }
                        } // foreach ($QrcodeList as $k => $v)
                        
                        // 当遍历结果为false的时候
                        // 或者遍历后的数组<=0的时候
                        // -------------------------
                        // 也就代表没有符合条件的二维码可以展示了
                        // 那就直接显示以下文案
                        if($foreachResult == false || count($QrcodeForeachList) <= 0){
                            
                            // 你也可以将下面的文字改成
                            // 暂时没有符合条件的二维码可展示
                            echo '<script>document.querySelector("#zhanwei").remove();</script>';
                            echo warnInfo('温馨提示','扫码次数已达上限');
                        }
                        
                    }else{
                        
                        // 随机模式
                        // 随机模式
                        // 随机模式
                        
                        // 将当前kf_id里面的所有微信二维码的数组成员进行随机打乱
                        shuffle($QrcodeList);
                        
                        // 遍历数组
                        foreach ($QrcodeList as $k => $v) {
                            
                            // 解析所需字段
                            $zm_id = $QrcodeList[$k]['zm_id']; // 微信二维码id
                            $zm_yz = $QrcodeList[$k]['zm_yz']; // 微信二维码阈值
                            $zm_pv = $QrcodeList[$k]['zm_pv']; // 微信二维码访问量
                            $zm_qrcode = $QrcodeList[$k]['zm_qrcode']; // 微信二维码图片地址
                            $zm_num = $QrcodeList[$k]['zm_num']; // 微信二维码的微信号
                            $zm_status = $QrcodeList[$k]['zm_status']; // 状态
                        
                            // 如果状态为1，才继续执行
                            if ($zm_status == 1) {
                                
                                // 顶部三件套
                                topMsg($kf_title, $kf_safety, $kf_beizhu);
                                
                                // 去重
                                if($kf_qc == 1) {
                                    
                                    // 读取cookie
                                    // 渲染当前设备第一次扫码展示的存储到cookie的子码
                                    if ($_COOKIE[$kid] && !empty($_COOKIE[$kid])) {
                                        
                                        echo '
                                        <p id="scanTips">请长按下方二维码添加微信</p>
                                        <div id="zm_qrcode" class="qrcode-view">
                                            <img src="'.$_COOKIE[$kid].'" />
                                        </div>';
                                        exit;
                                    }
                                }else {
                                    
                                    // 未开启去重
                                    // 渲染当前随机的子码
                                    echo '
                                    <p id="scanTips">请长按识别二维码添加微信</p>
                                    <div id="zm_qrcode" class="qrcode-view">
                                        <img src="'.$zm_qrcode.'" />
                                    </div>';
                                }
                                
                                // 如果微信号非空
                                if (!empty($zm_num)) {
                                    
                                    // 微信号
                                    echo 
                                    '<p id="wxnum">
                                        <span class="num">微信号 : '.$zm_num.'</span>
                                        <span class="copy" data-wxnum="'.$zm_num.'" onclick="copyWeChatNum(this)">复制</span>
                                    </p>';
                                }
                                
                                // 在线状态
                                if ($kf_online == 1) {
                                    
                                    // 开启
                                    // 传JSON配置
                                    showOnlineStatus($kf_onlinetimes);
                                }
                        
                                // 更新当前二维码的访问量（仅更新符合当前阈值条件的二维码的访问量）
                                updateKfQrcodePv($db, $zm_id, $zm_pv);
                                
                                // 将当前子码存储到cookie
                                if ($_COOKIE[$kid] == null) {

                                    $cookie_expire = time() + (30 * 24 * 60 * 60); // 30天
                                    setcookie($kid, $zm_qrcode, $cookie_expire);
                                }
                                
                                // 只输出第一个符合状态为1的二维码，立即跳出循环
                                break;
                            }
                        }
                    }
                }else{
                    
                    // 获取不到二维码
                    // 代表客服码创建之后
                    // 还没上传客服二维码
                    // 请在后台【···】点“上传”
                    echo warnInfo('温馨提示','管理员暂未上传二维码');
                } // if($getQrcodeListResult && $getQrcodeListResult > 0)
                
            }else{
                
                // 当前状态：停用
                // kf_status !== 1的情况
                echo warnInfo('温馨提示','该链接已被管理员暂停使用');
            } // if($kf_status == 1)
        }else{
            
            // 不存在
            // 获取不到该kf_id的详情
            echo warnInfo('温馨提示','该链接不存在或已被管理员删除');
        } // if($getKfInfoResult && $getKfInfoResult > 0)
        
    } // if($kid && $kid !== '')
    
    /**
     * 以下是封装的一些操作函数
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
            $getThisIpISFirstTimeToday = $db->set_table('huoma_ip_temp')->find(['create_date'=>date('Y-m-d'),'ip'=>$getIP,'from_page'=>'kf']);
            
            // 如果没有记录
            // 说明这个ip是今天第一次访问
            if(!$getThisIpISFirstTimeToday){
                
                // 将当前ip添加至临时ip表
                $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'kf']);
                
                // 然后更新今天的ip记录数
                $kf_ip = json_decode(json_encode($getTodayIpNum))->kf_ip;
                $newKf_ip = $kf_ip + 1;
                $db->set_table('huoma_ip')->update(['ip_create_time'=>date('Y-m-d')],['kf_ip'=>$newKf_ip]);
            }
        }else{
            
            // 如果没有记录
            // 将当前ip添加至临时ip表并记录为今天的ip访问
            $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'kf']);
            
            // 新增这个ip今天的访问次数
            $db->set_table('huoma_ip')->add(['kf_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
        }
        
        // 昨天的日期
        $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
        
        // 检查是否存在昨天的ip记录
        $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(['create_date'=>$yesterdayDate,'from_page'=>'kf']);
        
        // 如果有记录
        if($getYesterdayIp){
            
            // 清理昨天日期的临时ip
            $db->set_table('huoma_ip_temp')->delete(['create_date'=>$yesterdayDate,'from_page'=>'kf']);
        }
    }
    
    // 更新当前客服码的今天访问量
    function updateTodayPv($db,$kid){
        
        // 获取kf_today_pv字段并提取pv和date
        $getTodayKfPv = $db->set_table('huoma_kf')->find(['kf_id'=>$kid]);
        if($getTodayKfPv){
            
            // kf_today_pv的值
            $kf_today_pv = getSqlData($getTodayKfPv,'kf_today_pv');
            
            // pv的值
            $today_pv = json_decode($kf_today_pv,true)['pv'];
            
            // date的值
            $today_date = json_decode($kf_today_pv,true)['date'];
            
            // 检查这个记录是不是今天的
            if($today_date == date('Y-m-d')){
                
                // 如果是今天的
                // 更新pv的值
                $newToday_pv = $today_pv + 1;
                $db->set_table('huoma_kf')->update(
                    ['kf_id'=>$kid],
                    ['kf_today_pv'=>'{"pv":"'.$newToday_pv.'","date":"'.date('Y-m-d').'"}']
                );
            }else{
                
                // 如果不是今天的
                // 先将日期更新为今天的
                // 再更新今天pv的值
                $db->set_table('huoma_kf')->update(
                    ['kf_id'=>$kid],
                    ['kf_today_pv'=>'{"pv":"1","date":"'.date('Y-m-d').'"}']
                );
            }
        }
    }
    
    // 更新当前小时的总访问量
    function updateCurrentHourPageView($db,$hourNum_type){
        
        // 引入公共文件
        include '../../console/public/updateCurrentHourPageView.php';
    }
    
    // 更新当前客服码的总访问量
    function updateKfPv($db,$kid,$kf_pv){
        
        // 即kid的访问量
        $newKf_pv = $kf_pv + 1;
        $db->set_table('huoma_kf')->update(
            ['kf_id'=>$kid],
            ['kf_pv'=>$newKf_pv]
        );
    }
    
    // 更新当前所展示给用户的微信二维码的访问量
    function updateKfQrcodePv($db,$zm_id,$zm_pv){
        
        // 即当前zm_id的访问量
        $newZm_pv = $zm_pv + 1;
        $db->set_table('huoma_kf_zima')->update(
            ['zm_id'=>$zm_id],
            ['zm_pv'=>$newZm_pv]
        );
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
    
    // 顶部三件套（标题、扫码安全验证提示、备注）
    function topMsg($kf_title,$kf_safety,$kf_beizhu){
        
        // 标题
        echo '<title>'.$kf_title.'</title>';
        
        // 顶部扫码安全提示
        if($kf_safety == 1){
            
            // 开启
            echo '<div id="qun_safety_new"><img src="../../static/img/aqtips.png" class="aqimg" /></div>';
        }
        
        // 备注
        if(!empty($kf_beizhu)){
            
            // 显示备注内容
            echo '<div id="kf_beizhu">'.$kf_beizhu.'</div>';
        }
    }
    
    // 根据在线时间配置显示在线状态
    function showOnlineStatus($kf_onlinetimes){
        
        // 设置时区为北京时间（东八区）
        date_default_timezone_set('Asia/Shanghai');
        
        // 获取当前时间和星期
        $currentDayOfWeek = date('N');
        $currentHour = strtotime(date('H:i'));
        
        // 在线时间设置（从 JSON 配置中获取）
        $onlineConfigJson = $kf_onlinetimes;
        
        // JSON配置解码
        $onlineTime = json_decode($onlineConfigJson, true);
        
        // 检查当前时间是否在设置的在线时间范围内
        if (isset($onlineTime[$currentDayOfWeek])) {
            $dayConfig = $onlineTime[$currentDayOfWeek];
            
            $isOnline = false;
            
            // 遍历符合条件的
            foreach ($dayConfig as $timeRange) {
                
                list($start, $end) = explode('-', $timeRange);
                $start = strtotime($start);
                $end = strtotime($end);
                
                if ($currentHour >= $start && $currentHour <= $end) {
                    $isOnline = true;
                    break;
                }
            }
            
            // 根据状态显示文案
            if ($isOnline) {
                
                // 在线状态
                echo '<div id="kf_online_true">当前在线，可扫码加微信！</div>';
            } else {
                
                // 不在线状态
                echo '<div id="kf_online_false">当前不在线，可能回复较慢！</div>';
            }
        } else {
            
            // 在线状态
            echo '<div id="kf_online_false">当前不在线，可能回复较慢！</div>';
        }
    }

?>

</body>
</html>