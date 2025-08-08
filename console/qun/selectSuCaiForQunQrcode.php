<?php

    // 设置响应头
    header("Content-type:application/json");
    
    // 接收参数并过滤
    $qun_id = isset($_GET['qunid']) ? intval($_GET['qunid']) : 0;
    $sucai_id = isset($_GET['sucai_id']) ? intval($_GET['sucai_id']) : 0;
    
    if (!$qun_id || !$sucai_id) {
        echo json_encode([
            'code' => 204,
            'msg'  => '非法请求'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 启动 session
    session_start();
    
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 生成随机子码ID
    $zm_id = rand(100000, 999999);
    
    // 引入配置
    include '../Db.php';
    include '../public/publicConfig.php';
    
    try {
        // PDO连接
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 查询素材文件名
        $stmt = $pdo->prepare("SELECT sucai_filename FROM huoma_sucai WHERE sucai_id = :sucai_id LIMIT 1");
        $stmt->execute([':sucai_id' => $sucai_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$row || empty($row['sucai_filename'])) {
            echo json_encode([
                'code' => 202,
                'msg'  => '素材不存在或无文件名'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        $zm_qrcode = $imgPathUrl . $row['sucai_filename'];
        $now = date('Y-m-d H:i:s');
    
        // 插入新子码记录
        $insertSql = "INSERT INTO huoma_qun_zima (qun_id, zm_id, zm_qrcode, zm_update_time) VALUES (:qun_id, :zm_id, :zm_qrcode, :zm_update_time)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertResult = $insertStmt->execute([
            ':qun_id' => $qun_id,
            ':zm_id' => $zm_id,
            ':zm_qrcode' => $zm_qrcode,
            ':zm_update_time' => $now
        ]);
    
        if ($insertResult) {
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
    }