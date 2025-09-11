<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
    $static_time = time();
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="color-scheme" content="light dark">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <link rel="stylesheet" href="../../static/css/common.css?v=<?php echo $static_time; ?>">
        <link rel="stylesheet" href="../../static/css/bootstrap.min.css?v=<?php echo $static_time; ?>">
        <script type="text/javascript" src="../../static/js/qrcode.min.js?v=<?php echo $static_time; ?>"></script>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    </head>
    <script>
        (function() {
            const paramName = "t";
            const paramValue = Date.now();
            const url = new URL(window.location.href);
            if (!url.searchParams.has(paramName)) {
                url.searchParams.set(paramName, paramValue);
                window.location.replace(url.toString());
            }
        })();
    </script>
    <body>
        
    <?php
    
        // 页面编码
        header("Content-type:text/html;charset=utf-8");
        
        // 获取参数
        $key = trim($_GET['key'] ?? '');
        
        // 过滤参数
        if($key){
            
            // 数据库配置
            include '../console/Db.php';
            
            $db_host = $config['db_host'];
            $db_name = $config['db_name'];
            $db_user = $config['db_user'];
            $db_pass = $config['db_pass'];
            $folder = $config['folderNum'];
        
            try {
                // 创建 PDO 实例
                $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("数据库连接失败: " . $e->getMessage());
            }
        
            // 封装查询函数（取单条记录）
            function pdo_find($pdo, $table, $where) {
                $field = key($where);
                $value = $where[$field];
                $sql = "SELECT * FROM `$table` WHERE `$field` = :value LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':value' => $value]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        
            // 封装更新函数
            function pdo_update($pdo, $table, $where, $data) {
                $set = [];
                foreach($data as $k => $v) {
                    $set[] = "`$k`=:$k";
                }
                $setStr = implode(",", $set);
        
                $field = key($where);
                $whereVal = $where[$field];
        
                $sql = "UPDATE `$table` SET $setStr WHERE `$field`=:whereVal";
                $stmt = $pdo->prepare($sql);
                $data['whereVal'] = $whereVal;
                return $stmt->execute($data);
            }
        
            // 1. 群活码
            if($getQunInfo = pdo_find($pdo, 'huoma_qun', ['qun_key'=>$key])){
                echo '<title>加载中...</title>';
                jumpTo($folder,$getQunInfo['qun_rkym'],'qun','qid',$getQunInfo['qun_id']);
            }
            // 2. 客服码
            else if($getKefuInfo = pdo_find($pdo, 'huoma_kf', ['kf_key'=>$key])){
                echo '<title>加载中...</title>';
                jumpTo($folder,$getKefuInfo['kf_rkym'],'kf','kid',$getKefuInfo['kf_id']);
            }
            // 3. 渠道码
            else if($getChannelInfo = pdo_find($pdo, 'huoma_channel', ['channel_key'=>$key])){
                echo '<title>加载中...</title>';
                jumpTo($folder,$getChannelInfo['channel_rkym'],'channel','cid',$getChannelInfo['channel_id']);
            }
            // 4. 中间页
            else if($getZjyInfo = pdo_find($pdo, 'huoma_tbk', ['zjy_key'=>$key])){
                echo '<title>加载中...</title>';
                jumpTo($folder,$getZjyInfo['zjy_rkym'],'zjy','zid',$getZjyInfo['zjy_id']);
            }
            // 5. 多项单页
            else if($getMultiSPAInfo = pdo_find($pdo, 'huoma_tbk_mutiSPA', ['multiSPA_key'=>$key])){
                echo '<title>加载中...</title>';
                jumpTo($folder,$getMultiSPAInfo['multiSPA_rkym'],'multiSPA','mid',$getMultiSPAInfo['multiSPA_id']);
            }
            // 6. 并流
            else if($getbingliuForKey = pdo_find($pdo, 'ylb_qun_bingliu', ['before_qun_key'=>$key])){
                $bingliu_status = $getbingliuForKey['bingliu_status'];
                $later_qun_id = $getbingliuForKey['later_qun_id'];
                $bingliu_num = $getbingliuForKey['bingliu_num'];
        
                $getrkymForLaterQunId = pdo_find($pdo, 'huoma_qun', ['qun_id' => $later_qun_id]);
                $laterQun_rkym = $getrkymForLaterQunId['qun_rkym'];
        
                if($bingliu_status == 1) {
                    $newNum = $bingliu_num + 1;
                    pdo_update($pdo, 'ylb_qun_bingliu', ['before_qun_key' => $key], ['bingliu_num' => $newNum]);
                    jumpTo($folder,$laterQun_rkym,'qun','qid',$later_qun_id);
                } else {
                    echo warnInfo('温馨提示','链接不存在或已被管理员删除');
                }
            }
            else{
                echo warnInfo('温馨提示','链接不存在或已被管理员删除');
            }
        
        }else{
            // 参数为空
            echo warnInfo('温馨提示','请求参数为空');
        }
        
        
        // 跳转到落地页
        function jumpTo($folder,$rkym,$hmType,$hmidName,$hmid){
            if($folder == 1){
                $longUrl = $rkym.'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid.'&t='.time();
            }else{
                $longUrl = $rkym.'/'.redirectURL($folder).'/common/'.$hmType.'/redirect/?'.$hmidName.'='.$hmid.'&t='.time();
            }
            
            // JS跳转
            echo '<script>location.href="'.$longUrl.'";</script>';
        }
        
        // 目录级别
        function redirectURL($folder){
            if($folder == 2){
                return basename(dirname(dirname(__FILE__)));
            }else if($folder == 3){
                return basename(dirname(dirname(dirname(__FILE__)))).'/'.basename(dirname(dirname(__FILE__)));
            }else if($folder == 4){
                $oneFolder = basename(dirname(dirname(dirname(dirname(__FILE__))))).'/';
                $twoFolder = basename(dirname(dirname(dirname(__FILE__)))).'/';
                $threeFolder = basename(dirname(dirname(__FILE__)));
                return $oneFolder.$twoFolder.$threeFolder;
            }
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