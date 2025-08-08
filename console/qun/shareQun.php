<?php

    header("Content-type:application/json");
    
    session_start();
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg' => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $qun_id = isset($_GET['qun_id']) ? trim($_GET['qun_id']) : '';
    
    if (empty($qun_id)) {
        echo json_encode([
            'code' => 203,
            'msg' => '非法请求'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    include '../Db.php';
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 查询 huoma_qun 表中 qun_id 对应数据
        $stmt = $pdo->prepare("SELECT qun_rkym, qun_dlym, qun_key FROM huoma_qun WHERE qun_id = :qun_id LIMIT 1");
        $stmt->execute([':qun_id' => $qun_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($row) {
            $qun_rkym = $row['qun_rkym'];
            $qun_dlym = $row['qun_dlym'];
            $qun_key = $row['qun_key'];
    
            // 生成 longUrl
            $baseDir = dirname(dirname(dirname($qun_rkym . $_SERVER["REQUEST_URI"])));
            $longUrl = $baseDir . '/common/qun/redirect/?qid=' . $qun_id . '#' . base64_encode($qun_key);
    
            // 生成 shortUrl
            $shortUrl = rtrim($qun_dlym, '/') . '/s/' . $qun_key;
    
            // 生成 qrcodeUrl
            $qrcodeUrl = $baseDir . '/common/qun/redirect/?qid=' . $qun_id . '#' . base64_encode(time());
    
            echo json_encode([
                'code' => 200,
                'msg' => '获取成功',
                'longUrl' => $longUrl,
                'shortUrl' => $shortUrl,
                'qrcodeUrl' => $qrcodeUrl
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 204,
                'msg' => '获取失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg' => '数据库异常：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }