<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    // 登录校验
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 接收参数
    $before_qun_id   = trim($_POST['before_qun_id'] ?? '');
    $later_qun_id    = trim($_POST['later_qun_id'] ?? '');
    $before_qun_key  = trim($_POST['before_qun_key'] ?? '');
    
    // 参数校验
    if (empty($before_qun_id)) {
        echo json_encode([
            'code' => 203,
            'msg'  => '请输入原活码ID'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (empty($before_qun_key)) {
        echo json_encode([
            'code' => 203,
            'msg'  => '请输入原活码短网址Key'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (empty($later_qun_id)) {
        echo json_encode([
            'code' => 203,
            'msg'  => '请输入并入活码ID'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 引入数据库配置
    include '../Db.php';
    
    try {
        // 创建 PDO 实例
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 检查原活码是否未删除（存在就表示未删除）
        $stmt = $pdo->prepare("SELECT qun_id FROM huoma_qun WHERE qun_id = :qun_id");
        $stmt->execute([':qun_id' => $before_qun_id]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode([
                'code' => 202,
                'msg'  => '添加失败！该活码未删除，不能并流。'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 检查是否已被并流
        $stmt = $pdo->prepare("SELECT later_qun_id FROM ylb_qun_bingliu WHERE before_qun_id = :before_qun_id");
        $stmt->execute([':before_qun_id' => $before_qun_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            echo json_encode([
                'code' => 202,
                'msg'  => '添加失败！该活码已被并流至ID：' . $existing['later_qun_id']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 检查并入的活码是否存在
        $stmt = $pdo->prepare("SELECT qun_id FROM huoma_qun WHERE qun_id = :qun_id");
        $stmt->execute([':qun_id' => $later_qun_id]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode([
                'code' => 202,
                'msg'  => '并入的活码ID不存在'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 插入并流记录
        $bingliu_id = rand(100000, 999999);
        $stmt = $pdo->prepare("INSERT INTO ylb_qun_bingliu (bingliu_id, before_qun_id, before_qun_key, later_qun_id, createUser)
                               VALUES (:bingliu_id, :before_qun_id, :before_qun_key, :later_qun_id, :createUser)");
        $res = $stmt->execute([
            ':bingliu_id'     => $bingliu_id,
            ':before_qun_id'  => $before_qun_id,
            ':before_qun_key' => $before_qun_key,
            ':later_qun_id'   => $later_qun_id,
            ':createUser'     => $_SESSION["yinliubao"]
        ]);
    
        if ($res) {
            echo json_encode([
                'code' => 200,
                'msg'  => '添加成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 202,
                'msg'  => '添加失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库异常：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
