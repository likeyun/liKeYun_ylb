<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 获取参数
    $bingliu_id = trim($_GET['bingliu_id'] ?? '');
    
    // 校验参数
    if (empty($bingliu_id)) {
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
        // 创建 PDO 实例
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 查询该记录
        $stmt = $pdo->prepare("SELECT createUser, bingliu_status FROM ylb_qun_bingliu WHERE bingliu_id = :bingliu_id");
        $stmt->execute([':bingliu_id' => $bingliu_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$record || $record['createUser'] !== $LoginUser) {
            echo json_encode([
                'code' => 202,
                'msg'  => '非法请求'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 计算新状态
        $new_status = ($record['bingliu_status'] == 1) ? 2 : 1;
        $status_text = ($new_status == 1) ? '已启用' : '已停用';
    
        // 执行更新
        $updateStmt = $pdo->prepare("UPDATE ylb_qun_bingliu SET bingliu_status = :status WHERE bingliu_id = :bingliu_id AND createUser = :user");
        $updateResult = $updateStmt->execute([
            ':status'      => $new_status,
            ':bingliu_id'  => $bingliu_id,
            ':user'        => $LoginUser
        ]);
    
        if ($updateResult) {
            echo json_encode([
                'code' => 200,
                'msg'  => $status_text
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 202,
                'msg'  => '操作失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库异常：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
