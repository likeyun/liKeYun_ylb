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
        <script src="https://cdn.staticfile.org/jquery/3.6.3/jquery.js"></script>
    </head>
    
    <body style="background:#fff;">
        
    <?php
    
        // 页面编码
        header("Content-type:text/html;charset=utf-8");
        
        // 获取参数
        $zid = trim(intval($_GET['zid']));
        
        // 过滤参数
        if($zid){
            
            // 数据库配置
            include '../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);
            
            // 获取中间页信息
            $getZjyInfo = $db->set_table('huoma_tbk')->find(['zjy_id'=>$zid]);
            
            // 验证该中间页是否存在
            if($getZjyInfo && $getZjyInfo > 0){
                
                // 解析所需字段
                // （1）短标题
                $zjy_short_title = getSqlData($getZjyInfo,'zjy_short_title');
                
                // （2）长标题
                $zjy_long_title = getSqlData($getZjyInfo,'zjy_long_title');
                
                // （3）原价
                $zjy_original_cost = getSqlData($getZjyInfo,'zjy_original_cost');
                
                // （4）券后价
                $zjy_discounted_price = getSqlData($getZjyInfo,'zjy_discounted_price');
                
                // （5）淘口令
                $zjy_tkl = getSqlData($getZjyInfo,'zjy_tkl');
                
                // （6）访问次数
                $zjy_pv = getSqlData($getZjyInfo,'zjy_pv');
                
                // （7）商品主图
                $zjy_goods_img = getSqlData($getZjyInfo,'zjy_goods_img');
                
                // （8）商品链接
                $zjy_goods_link = getSqlData($getZjyInfo,'zjy_goods_link');
                
                // 更新中间页的访问量
                updateZjyPv($db,$zid);
                
                // 记录今天ip访问量
                updateTodayIpNum($db);
                
                // 更新当前小时的总访问量
                updateCurrentHourPageView($db,'zjy');
                
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
                // 获取不到详情
                echo warnInfo('温馨提示','页面不存在或已被管理员删除');
            } // if($getZjyInfo && $getZjyInfo > 0)
            
        } // if($zid && $zid !== '')
        
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
                $getThisIpISFirstTimeToday = $db->set_table('huoma_ip_temp')->find(['create_date'=>date('Y-m-d'),'ip'=>$getIP,'from_page'=>'zjy']);
                
                // 如果没有记录
                // 说明这个ip是今天第一次访问
                if(!$getThisIpISFirstTimeToday){
                    
                    // 将当前ip添加至临时ip表
                    $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'zjy']);
                    
                    // 然后更新今天的ip记录数
                    $zjy_ip = json_decode(json_encode($getTodayIpNum))->zjy_ip;
                    $newZjy_ip = $zjy_ip + 1;
                    $db->set_table('huoma_ip')->update(['ip_create_time'=>date('Y-m-d')],['zjy_ip'=>$newZjy_ip]);
                }
            }else{
                
                // 如果没有记录
                // 将当前ip添加至临时ip表并记录为今天的ip访问
                $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'zjy']);
                
                // 新增这个ip今天的访问次数
                $db->set_table('huoma_ip')->add(['zjy_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
            }
            
            // 昨天的日期
            $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
            
            // 检查是否存在昨天的ip记录
            $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(['create_date'=>$yesterdayDate,'from_page'=>'zjy']);
            
            // 如果有记录
            if($getYesterdayIp){
                
                // 清理昨天日期的临时ip
                $db->set_table('huoma_ip_temp')->delete(['create_date'=>$yesterdayDate,'from_page'=>'zjy']);
            }
        }
        
        // 更新当前小时的总访问量
        function updateCurrentHourPageView($db,$hourNum_type){
            
            // 引入公共文件
            include '../../console/public/updateCurrentHourPageView.php';
        }
        
        // 更新中间页的访问量
        function updateZjyPv($db,$zid){
            
            $updateZjyPv = 'UPDATE huoma_tbk SET zjy_pv=zjy_pv+1 WHERE zjy_id="'.$zid.'"';
            $db->set_table('huoma_tbk')->findSql($updateZjyPv);
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
            
            // 隐藏提示
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