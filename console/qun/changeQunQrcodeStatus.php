<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    // 验证登录
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 接收 zm_id 参数
    $zm_id = isset($_GET['zm_id']) ? trim($_GET['zm_id']) : '';
    if (empty($zm_id)) {
        echo json_encode([
            'code' => 203,
            'msg'  => '非法请求'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 当前用户
    $LoginUser = $_SESSION["yinliubao"];
    
    // 数据库配置
    include '../Db.php';
    
    try {
        // 建立 PDO 连接
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 获取该 zm_id 所属的 qun_id
        $stmtZima = $pdo->prepare("SELECT qun_id, zm_status FROM huoma_qun_zima WHERE zm_id = :zm_id");
        $stmtZima->execute([':zm_id' => $zm_id]);
        $zimaData = $stmtZima->fetch(PDO::FETCH_ASSOC);
    
        if (!$zimaData) {
            echo json_encode([
                'code' => 202,
                'msg'  => '子码不存在或已被删除'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        $qun_id = $zimaData['qun_id'];
        $currentStatus = intval($zimaData['zm_status']);
    
        // 获取 qun 所属用户
        $stmtQun = $pdo->prepare("SELECT qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
        $stmtQun->execute([':qun_id' => $qun_id]);
        $qunData = $stmtQun->fetch(PDO::FETCH_ASSOC);
    
        if (!$qunData || $qunData['qun_creat_user'] !== $LoginUser) {
            echo json_encode([
                'code' => 202,
                'msg'  => '非法请求'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 切换状态
        $newStatus = ($currentStatus === 1) ? 2 : 1;
        $statusText = ($newStatus === 1) ? '已启用' : '已停用';
    
        // 执行更新
        $updateStmt = $pdo->prepare("UPDATE huoma_qun_zima SET zm_status = :new_status WHERE zm_id = :zm_id");
        $updated = $updateStmt->execute([
            ':new_status' => $newStatus,
            ':zm_id'      => $zm_id
        ]);
    
        if ($updated) {
            echo json_encode([
                'code'      => 200,
                'zm_status' => $newStatus,
                'msg'       => $statusText
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
