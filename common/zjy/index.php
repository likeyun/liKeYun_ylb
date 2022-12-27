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
        <!--公共JS-->
        <script src="../../static/js/jquery.min.js"></script>
    </head>
<body style="background:#fff;">
    
<?php

    // 页面编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数（intval函数用于过滤特殊字符防止SQL注入）
    $zid = trim(intval($_GET['zid']));
    
    // 过滤参数
    if($zid && $zid !== ''){
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 获取中间页信息
        $getZjyInfo = ['zjy_id'=>$zid];
        $getZjyInfoResult = $db->set_table('huoma_tbk')->find($getZjyInfo);
        
        // 验证该中间页是否存在
        if($getZjyInfoResult && $getZjyInfoResult > 0){
            
            // 存在
            // 解析所需字段
            // （1）短标题
            $zjy_short_title = getSqlData($getZjyInfoResult,'zjy_short_title');
            
            // （2）长标题
            $zjy_long_title = getSqlData($getZjyInfoResult,'zjy_long_title');
            
            // （3）原价
            $zjy_original_cost = getSqlData($getZjyInfoResult,'zjy_original_cost');
            
            // （4）券后价
            $zjy_discounted_price = getSqlData($getZjyInfoResult,'zjy_discounted_price');
            
            // （5）淘口令
            $zjy_tkl = getSqlData($getZjyInfoResult,'zjy_tkl');
            
            // （6）访问次数
            $zjy_pv = getSqlData($getZjyInfoResult,'zjy_pv');
            
            // （7）商品主图
            $zjy_goods_img = getSqlData($getZjyInfoResult,'zjy_goods_img');
            
            // （8）商品链接
            $zjy_goods_link = getSqlData($getZjyInfoResult,'zjy_goods_link');
            
            // 更新当前中间页的访问量
            updateThisZjyPv($db,$zid);
            
            // 更新数据统计表访问量
            updateCountChartPv($db);
            
            // 短标题
            echo '<title>粉丝福利购</title>';
            echo '<div id="zjy_short_title">'.$zjy_short_title.'</div>';
            
            // 商品主图
            echo '<div id="zjy_goods_img"><img src="'.$zjy_goods_img.'" /></div>';
            
            // 长标题
            echo '
            <div id="zjy_long_title">
                <span class="baoyou">包邮</span>
                <span class="long_title">'.$zjy_long_title.'</span>
            </div>';
            
            // 价格
            echo '
            <div id="zjy_price">
                <div class="qhj">
                    <span class="quanhoujia">券后价</span>
                </div>
                <div class="zjy_discounted_price">¥'.$zjy_discounted_price.'</div>
                <div class="zjy_original_cost">原价 <s>¥'.$zjy_original_cost.'</s></div>
                <div class="zjy_pv">已有'.$zjy_pv.'人购买</div>
            </div>';
            
            // 淘口令
            echo '
            <div id="zjy_tkl">
                <span class="tkl_text">
                    <span id="tkl_text" class="tklstr">'.rand(1,9).str_replace('￥','$',$zjy_tkl).":// CZ".rand(1000,9999).'</span>
                    <span class="dot1"></span>
                    <span class="dot2"></span>
                    <span class="dot3"></span>
                    <span class="dot4"></span>
                    <span class="dot5"></span>
                    <span class="dot6"></span>
                    <span class="dot7"></span>
                </span>
                <button class="copy_btn" id="copy" data-clipboard-text="'.rand(1,9).str_replace('￥','$',$zjy_tkl).":// CZ".rand(1000,9999).'">一键复制</button>
            </div>';
            
            // 提示文字
            echo '<p id="tipsText">复制淘口令 -> 打开手机淘宝APP即可</p>';
            
            // 复制反馈
            echo '
            <div id="alertModal">
                <span>已复制</span>
                <span>请打开淘宝APP</span>
            </div>';
            
        }else{
            
            // 不存在
            // 获取不到该channel_id的详情
            echo '<title>温馨提示</title>';
            echo warnningInfo('页面不存在或已被管理员删除');
        } // if($getZjyInfoResult && $getZjyInfoResult > 0)
        
    } // if($zid && $zid !== '')
    
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
        $updatePv = 'UPDATE huoma_count SET count_zjy_pv=count_zjy_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // （1）将日期更新为今天并且访问量归零
    // （2）更新当前小时的访问量
    function updateDefault($huoma_count){
        
        $thisDate = date('Y-m-d');
        $updateDefault = 'UPDATE huoma_count SET count_qun_pv="0",count_kf_pv="0",count_channel_pv="0",count_dwz_pv="0",count_zjy_pv="0",count_date="'.$thisDate.'"';
        $huoma_count->findSql($updateDefault);
        $thisHour = date('H');
        $updatePv = 'UPDATE huoma_count SET count_zjy_pv=count_zjy_pv+1 WHERE count_hour="'.$thisHour.'"';
        $huoma_count->findSql($updatePv);
    }
    
    // 更新当前中间页的访问量
    function updateThisZjyPv($db,$zid){
        
        // 传入zid
        $updateThisZjyPv = 'UPDATE huoma_tbk SET zjy_pv=zjy_pv+1 WHERE zjy_id="'.$zid.'"';
        $db->set_table('huoma_tbk')->findSql($updateThisZjyPv);
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

<script src="../../static/js/clipboard.min.js"></script>
<script>

    // clipboard插件
    var clipboard = new ClipboardJS('#copy');
    
    clipboard.on('success', function(e) {
        console.info('Action:', e.action);
        console.info('Text:', e.text);
        console.info('Trigger:', e.trigger);
        
        // 记录复制次数
        copyNum();
        
        // 显示提示
        $('#alertModal').css('display','block');
        
        // 2.5秒隐藏提示
        setTimeout("$('#alertModal').css('display','none')", 2500);
        
        // 如果是iPhone手机复制后询问是否要直跳手机淘宝APP领券页面
        var ua = navigator.userAgent.toLowerCase();
        var zjy_goods_link = "<?php echo $zjy_goods_link; ?>";
        if (/\(i[^;]+;( U;)? CPU.+Mac OS X/gi.test(ua) && zjy_goods_link !== '') {
            
            // 直跳手机淘宝APP领券页面
            window.location.href = zjy_goods_link;
        }
        
        // 清除剪贴板
        e.clearSelection();
        
    });
    
    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });
	
    // 记录复制次数
    function copyNum(){
        
        var zjy_id = "<?php echo $zid; ?>";
        $.ajax({
            type: "GET",
            url: "../../console/tbk/copyNum.php?zjy_id="+zjy_id
        });
    }
</script>
</body>
</html>