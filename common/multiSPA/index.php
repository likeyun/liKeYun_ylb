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
    
    <body style="background:#f1f1f1;">
        
    <?php

        /**
         * 标题：多项单页公共页面
         * 维护：2023年7月31日
         * 作者：TANKING
         * 博客：https://segmentfault.com/u/tanking
         * 摘要：新增
         */
    
        // 页面编码
        header("Content-type:text/html;charset=utf-8");
        
        // 获取参数
        $mid = trim(intval($_GET['mid']));
        
        // 过滤参数
        if($mid && $mid !== ''){
            
            // 数据库配置
            include '../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);
            
            // 获取单页信息
            $getMutiSPAInfo = $db->set_table('huoma_tbk_mutiSPA')->find(['multiSPA_id'=>$mid]);
            
            // 验证该单页是否存在
            if($getMutiSPAInfo){
                
                // 存在
                $multiSPA_title = getSqlData($getMutiSPAInfo,'multiSPA_title');
                $multiSPA_project = getSqlData($getMutiSPAInfo,'multiSPA_project');
                $multiSPA_img = getSqlData($getMutiSPAInfo,'multiSPA_img');
                $multiSPA_pv = getSqlData($getMutiSPAInfo,'multiSPA_pv');
                
                // 更新当前单页的访问量
                updateThisMultiSPAPv($db,$mid,$multiSPA_pv);
                
                // 更新当前小时的总访问量
                updateCurrentHourPageView($db,'multiSPA');
                
                // 记录今天ip访问量
                updateTodayIpNum($db);
                
                // 标题
                echo '<title>'.$multiSPA_title.'</title>' . PHP_EOL;
                echo '<div id="multiSPA_title">'.$multiSPA_title.'</div>' . PHP_EOL;
                
                // 占位
                echo '<div id="multiSPA_zhanwei"></div>' . PHP_EOL;
                
                // 主图
                if($multiSPA_img){
                    
                    // 如果有上传才会展示
                    echo '<div id="multiSPA_img">' . PHP_EOL . '<img src="'.$multiSPA_img.'" />' . PHP_EOL . '</div>' . PHP_EOL;
                }
                
                // 项目HTML
                echo '<div id="multiSPA_project">' . PHP_EOL . $multiSPA_project . PHP_EOL . '</div>' . PHP_EOL;
                
                // 底部占位
                echo '<div id="multiSPA_bottom_zhanwei"></div>' . PHP_EOL;
                
                // 复制成功后显示的Modal
                echo '<div id="copyModal"></div>';
                
            }else{
                
                // 不存在
                echo warnInfo('温馨提示','页面不存在或已被管理员删除');
            } // if($getMutiSPAInfo && $getMutiSPAInfo > 0){
            
        } // if($mid && $mid !== '')
        
        /**
         * 以下是封装的一些操作函数
         * 1. 便于多处调用
         * 2. 保持代码整洁可读
         */
        
        // 更新当前单页的访问量
        function updateThisMultiSPAPv($db,$mid,$multiSPA_pv){
            
            // 更新访问量
            $newMultiSPA_pv = $multiSPA_pv + 1;
            $updateMultiSPAPV = $db->set_table('huoma_tbk_mutiSPA')->update(
                ['multiSPA_id'=>$mid],
                ['multiSPA_pv'=>$newMultiSPA_pv]
            );
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
                        'from_page'=>'multiSPA'
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
                            'from_page'=>'multiSPA'
                        ]
                    );
                    
                    // 然后更新今天的ip记录数
                    $multiSPA_ip = json_decode(json_encode($getTodayIpNum))->multiSPA_ip;
                    $newMultiSPA_ip = $multiSPA_ip + 1;
                    $db->set_table('huoma_ip')->update(
                        ['ip_create_time'=>date('Y-m-d')],
                        ['multiSPA_ip'=>$newMultiSPA_ip]
                    );
                }
            }else{
                
                // 如果没有记录
                // 将当前ip添加至临时ip表并记录为今天的ip访问
                $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'multiSPA']);
                
                // 新增这个ip今天的访问次数
                $db->set_table('huoma_ip')->add(['multiSPA_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
            }
            
            // 昨天的日期
            $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
            
            // 检查是否存在昨天的ip记录
            $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(['create_date'=>$yesterdayDate,'from_page'=>'multiSPA']);
            
            // 如果有记录
            if($getYesterdayIp){
                
                // 清理昨天日期的临时ip
                $db->set_table('huoma_ip_temp')->delete(['create_date'=>$yesterdayDate,'from_page'=>'multiSPA']);
            }
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 获取所有<c>标签
        const cTags = document.getElementsByTagName('c');
        
        // 为每个<c>标签添加点击事件监听器
        for (const cTag of cTags) {
            
            cTag.addEventListener('click', function () {
                
                // 获取<c>标签里面的data内容
                const data = cTag.getAttribute('data');
                
                // 复制按钮的文字
                var copyButtonText = cTag.innerText;
                
                // 创建一个临时textarea元素
                const textarea = document.createElement('textarea');
                textarea.value = data;
                
                // 将textarea元素添加到页面中
                document.body.appendChild(textarea);
                
                // 选中textarea内容
                textarea.select();
                
                // 复制选中的内容到剪贴板
                document.execCommand('copy');
                
                // 移除临时textarea元素
                document.body.removeChild(textarea);
                
                // 告知复制结果
                cTag.innerHTML = '已复制';
                
                // 3秒后恢复
                setTimeout(function() {
                    cTag.innerHTML = copyButtonText;
                }, 3000);
                
                // 显示Modal
                copyModal('已复制');
            });
        }
    });
    
    // 复制成功后显示的Modal
    function copyModal(text) {
        
        // 获取copyModal
        var copyModal = document.getElementById("copyModal");
        
        // 设置内容
        copyModal.innerText = text;
        
        // 显示
        copyModal.style.display = "block";
        
        // 秒后隐藏
        setTimeout(function() {
            copyModal.style.display = "none";
        }, 3000);
    }
    
    </script>
    </body>
</html>