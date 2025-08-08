<?php

    // 设置响应头
    header("Content-type:application/json");
    
    // 接收并过滤素材ID
    $sucai_id = isset($_GET['sucai_id']) ? intval($_GET['sucai_id']) : 0;
    
    if (!$sucai_id) {
        echo json_encode([
            'code' => 204,
            'msg'  => '非法请求'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 启动会话
    session_start();
    
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 引入配置
    include '../Db.php';
    include '../public/publicConfig.php';
    
    try {
        // 创建PDO连接
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
    
        if ($row && !empty($row['sucai_filename'])) {
            echo json_encode([
                'code' => 200,
                'msg'  => '获取成功',
                'qunQrcodeUrl' => $imgPathUrl . $row['sucai_filename']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 202,
                'msg'  => '获取失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库异常：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
