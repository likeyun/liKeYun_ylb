<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    // 判断登录
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 获取参数
    $bingliu_id = isset($_GET['bingliu_id']) ? trim($_GET['bingliu_id']) : '';
    
    if (empty($bingliu_id)) {
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
        // 创建 PDO 实例
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 验证权限
        $stmt = $pdo->prepare("SELECT createUser FROM ylb_qun_bingliu WHERE bingliu_id = :bingliu_id");
        $stmt->execute([':bingliu_id' => $bingliu_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$row) {
            echo json_encode([
                'code' => 202,
                'msg'  => '删除失败：记录不存在'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        if ($row['createUser'] !== $LoginUser) {
            echo json_encode([
                'code' => 202,
                'msg'  => '删除失败：鉴权失败！'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 删除操作
        $delStmt = $pdo->prepare("DELETE FROM ylb_qun_bingliu WHERE bingliu_id = :bingliu_id AND createUser = :createUser");
        $delResult = $delStmt->execute([
            ':bingliu_id' => $bingliu_id,
            ':createUser' => $LoginUser
        ]);
    
        if ($delResult) {
            echo json_encode([
                'code' => 200,
                'msg'  => '已删除'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 202,
                'msg'  => '删除失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库错误：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
