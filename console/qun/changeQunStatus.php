<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    // 判断登录状态
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 接收参数
    $qun_id = isset($_GET['qun_id']) ? trim($_GET['qun_id']) : '';
    
    if (empty($qun_id)) {
        echo json_encode([
            'code' => 203,
            'msg'  => '非法请求'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 当前登录用户
    $LoginUser = $_SESSION["yinliubao"];
    
    // 引入数据库配置
    include '../Db.php';
    
    try {
        // 创建 PDO 实例
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 查询群信息
        $stmt = $pdo->prepare("SELECT qun_status, qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
        $stmt->execute([':qun_id' => $qun_id]);
        $qunData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$qunData) {
            echo json_encode([
                'code' => 202,
                'msg'  => '群不存在'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 验证创建者
        if ($qunData['qun_creat_user'] !== $LoginUser) {
            echo json_encode([
                'code' => 202,
                'msg'  => '非法请求'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 切换状态
        $currentStatus = intval($qunData['qun_status']);
        $newStatus = ($currentStatus === 1) ? 2 : 1;
        $statusText = ($newStatus === 1) ? '已启用' : '已停用';
    
        // 更新状态
        $update = $pdo->prepare("UPDATE huoma_qun SET qun_status = :status WHERE qun_id = :qun_id AND qun_creat_user = :user");
        $result = $update->execute([
            ':status' => $newStatus,
            ':qun_id' => $qun_id,
            ':user'   => $LoginUser
        ]);
    
        if ($result) {
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
            'msg'  => '数据库异常：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }