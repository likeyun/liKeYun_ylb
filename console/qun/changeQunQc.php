<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    // 登录验证
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 接收参数
    $qun_id = isset($_GET['qun_id']) ? intval(trim($_GET['qun_id'])) : 0;
    if ($qun_id <= 0) {
        echo json_encode([
            'code' => 203,
            'msg'  => '非法请求'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 当前登录用户
    $LoginUser = $_SESSION["yinliubao"];
    
    // 数据库配置
    include '../Db.php';
    
    try {
        // 建立 PDO 连接
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 获取该 qun 的创建者和当前 qun_qc 状态
        $stmt = $pdo->prepare("SELECT qun_creat_user, qun_qc FROM huoma_qun WHERE qun_id = :qun_id");
        $stmt->execute([':qun_id' => $qun_id]);
        $qunData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$qunData || $qunData['qun_creat_user'] !== $LoginUser) {
            echo json_encode([
                'code' => 202,
                'msg'  => '非法请求'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 获取当前状态并反转
        $currentStatus = intval($qunData['qun_qc']);
        $newStatus = ($currentStatus === 1) ? 2 : 1;
        $statusText = ($newStatus === 1) ? '已启用' : '已停用';
    
        // 执行更新
        $updateStmt = $pdo->prepare("
            UPDATE huoma_qun 
            SET qun_qc = :new_qc 
            WHERE qun_id = :qun_id AND qun_creat_user = :user
        ");
        $updateSuccess = $updateStmt->execute([
            ':new_qc'  => $newStatus,
            ':qun_id'  => $qun_id,
            ':user'    => $LoginUser
        ]);
    
        if ($updateSuccess) {
            echo json_encode([
                'code' => 200,
                'msg'  => $statusText
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 202,
                'msg'  => '更新失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库错误：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
