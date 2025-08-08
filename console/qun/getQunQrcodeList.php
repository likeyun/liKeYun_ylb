<?php

    // 设置响应类型
    header("Content-type:application/json");
    
    // 启动 session
    session_start();
    
    if (isset($_SESSION["yinliubao"])) {
    
        // 获取 qun_id
        $qun_id = isset($_GET['qun_id']) ? intval($_GET['qun_id']) : 0;
    
        // 校验参数
        if ($qun_id <= 0) {
            echo json_encode([
                'code' => 203,
                'msg' => '非法请求'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 引入数据库配置
        include '../Db.php';
    
        try {
            // 创建 PDO 实例
            $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // 获取群标题
            $stmt = $pdo->prepare("SELECT qun_title FROM huoma_qun WHERE qun_id = :qun_id");
            $stmt->execute([':qun_id' => $qun_id]);
            $qun = $stmt->fetch(PDO::FETCH_ASSOC);
            $qun_title = $qun ? $qun['qun_title'] : '';
    
            // 获取子码列表
            $stmt = $pdo->prepare("SELECT * FROM huoma_qun_zima WHERE qun_id = :qun_id ORDER BY ID ASC");
            $stmt->execute([':qun_id' => $qun_id]);
            $qunQrcodeList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($qunQrcodeList) {
                $result = [
                    'code' => 200,
                    'msg' => 'SUCCESS',
                    'qun_title' => $qun_title,
                    'qunQrcodeList' => $qunQrcodeList
                ];
            } else {
                $result = [
                    'code' => 204,
                    'msg' => '暂无群二维码',
                    'qun_title' => $qun_title
                ];
            }
    
        } catch (PDOException $e) {
            $result = [
                'code' => 500,
                'msg' => '数据库连接失败: ' . $e->getMessage()
            ];
        }
    
    } else {
        // 未登录
        $result = [
            'code' => 201,
            'msg' => '未登录'
        ];
    }
    
    // 输出 JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>