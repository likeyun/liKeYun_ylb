<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    // 判断是否登录
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 获取参数
    $qun_id = isset($_GET['qun_id']) ? trim($_GET['qun_id']) : '';
    
    if (empty($qun_id)) {
        echo json_encode([
            'code' => 203,
            'msg'  => '非法请求'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 当前用户
    $LoginUser = $_SESSION["yinliubao"];
    
    // 引入数据库配置
    include '../Db.php';
    
    try {
        // 实例化 PDO
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 查询群信息，校验用户权限
        $stmt = $pdo->prepare("SELECT qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
        $stmt->execute([':qun_id' => $qun_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$row) {
            echo json_encode([
                'code' => 201,
                'msg'  => '群不存在'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        if ($row['qun_creat_user'] !== $LoginUser) {
            echo json_encode([
                'code' => 201,
                'msg'  => '不允许操作'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 构建更新
        $today = date('Y-m-d');
        $qun_today_pv = json_encode([
            'pv'   => "0",
            'date' => $today
        ], JSON_UNESCAPED_UNICODE);
    
        $update = $pdo->prepare("UPDATE huoma_qun SET qun_pv = 0, qun_today_pv = :qun_today_pv WHERE qun_id = :qun_id AND qun_creat_user = :qun_creat_user");
        $updated = $update->execute([
            ':qun_today_pv'    => $qun_today_pv,
            ':qun_id'          => $qun_id,
            ':qun_creat_user'  => $LoginUser
        ]);
    
        if ($updated) {
            echo json_encode([
                'code' => 201,
                'msg'  => '已重置'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 201,
                'msg'  => '重置失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库错误：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }