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
    
    // 接收参数
    $zm_id = isset($_GET['zm_id']) ? trim($_GET['zm_id']) : '';
    if (empty($zm_id)) {
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
        // 建立 PDO 连接
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 获取 zm_id 对应的 qun_id
        $stmtZima = $pdo->prepare("SELECT zm_pv, qun_id FROM huoma_qun_zima WHERE zm_id = :zm_id");
        $stmtZima->execute([':zm_id' => $zm_id]);
        $zimaData = $stmtZima->fetch(PDO::FETCH_ASSOC);
    
        if (!$zimaData) {
            echo json_encode([
                'code' => 202,
                'msg'  => '重置失败：无法获取到数据，原因：数据已被删除、数据不存在、获取数据失败等...'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        $qun_id = $zimaData['qun_id'];
        $zm_pv = (int)$zimaData['zm_pv'];
    
        // 获取 qun 创建者
        $stmtQun = $pdo->prepare("SELECT qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
        $stmtQun->execute([':qun_id' => $qun_id]);
        $qunData = $stmtQun->fetch(PDO::FETCH_ASSOC);
    
        if (!$qunData || $qunData['qun_creat_user'] !== $LoginUser) {
            echo json_encode([
                'code' => 202,
                'msg'  => '重置失败：无法获取到数据，原因：数据已被删除、数据不存在、获取数据失败等...'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 判断是否需要重置
        if ($zm_pv === 0) {
            echo json_encode([
                'code' => 202,
                'msg'  => '访问量为0无需重置'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 执行重置
        $update = $pdo->prepare("
            UPDATE huoma_qun_zima 
            SET zm_yz = '200', zm_pv = 0, zm_update_time = :update_time 
            WHERE zm_id = :zm_id
        ");
        $reset = $update->execute([
            ':update_time' => date('Y-m-d H:i:s'),
            ':zm_id'       => $zm_id
        ]);
    
        if ($reset) {
            echo json_encode([
                'code'    => 200,
                'msg'     => '重置成功',
                'qun_id'  => $qun_id
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 202,
                'msg'  => '重置失败，原因：更新操作失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库错误：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }