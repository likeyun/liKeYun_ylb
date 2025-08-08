<?php
    
    // 设置响应类型
    header("Content-type:application/json");
    
    // 启动 session
    session_start();
    
    if (isset($_SESSION["yinliubao"])) {
    
        // 接收参数
        $qun_title = trim(htmlspecialchars($_POST['qun_title']));
        $qun_rkym = trim($_POST['qun_rkym']);
        $qun_ldym = trim($_POST['qun_ldym']);
        $qun_dlym = trim($_POST['qun_dlym']);
        $qun_creat_user = trim($_SESSION["yinliubao"]);
    
        // 参数校验
        if (empty($qun_title)) {
            echo json_encode([
                'code' => 203, 
                'msg' => '群标题未设置'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        if (empty($qun_rkym)) {
            echo json_encode([
                'code' => 203, 
                'msg' => '入口域名未选择'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        if (empty($qun_ldym)) {
            echo json_encode([
                'code' => 203, 
                'msg' => '落地域名未选择'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        if (empty($qun_dlym)) {
            echo json_encode([
                'code' => 203, 
                'msg' => '短链域名未选择'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    
        // 生成 qun_id
        $qun_id = '10' . mt_rand(1000, 9999);
    
        function creatKey($length = 5) {
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
            return substr(str_shuffle($str), 0, $length);
        }
        
        // 生成 qun_key
        $qun_key = creatKey();
    
        // 引入数据库配置
        include '../Db.php';
    
        try {
            // PDO 连接
            $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // 插入语句
            $sql = "INSERT INTO huoma_qun (qun_title, qun_today_pv, qun_rkym, qun_ldym, qun_dlym, qun_creat_user, qun_key, qun_id)
                    VALUES (:qun_title, :qun_today_pv, :qun_rkym, :qun_ldym, :qun_dlym, :qun_creat_user, :qun_key, :qun_id)";
            $stmt = $pdo->prepare($sql);
    
            $todayPvJson = json_encode([
                'pv' => 0,
                'date' => date("Y-m-d")
            ], JSON_UNESCAPED_UNICODE);
    
            $res = $stmt->execute([
                ':qun_title' => $qun_title,
                ':qun_today_pv' => $todayPvJson,
                ':qun_rkym' => $qun_rkym,
                ':qun_ldym' => $qun_ldym,
                ':qun_dlym' => $qun_dlym,
                ':qun_creat_user' => $qun_creat_user,
                ':qun_key' => $qun_key,
                ':qun_id' => $qun_id
            ]);
    
            if ($res) {
                echo json_encode([
                    'code' => 200,
                    'msg' => '创建成功',
                    'qun_id' => $qun_id
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'code' => 202,
                    'msg' => '创建失败'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
    
        } catch (PDOException $e) {
            echo json_encode([
                'code' => 500,
                'msg' => '数据库错误：' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    
    } else {
        
        // 未登录
        echo json_encode([
            'code' => 201,
            'msg' => '未登录'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
?>