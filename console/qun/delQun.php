<?php

    header("Content-type:application/json");
    
    // 启动 session
    session_start();
    
    if (isset($_SESSION["yinliubao"])) {
    
        // 接收参数
        $qun_id = trim($_GET['qun_id']);
    
        if (empty($qun_id)) {
            $result = ['code' => 203, 'msg' => '非法请求'];
        } else {
    
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
    
            // 数据库配置
        	include '../Db.php';
        	
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    
            try {
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
    
                // 验证用户
                $stmt = $pdo->prepare("SELECT qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
                $stmt->execute(['qun_id' => $qun_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($row && $row['qun_creat_user'] === $LoginUser) {
    
                    // 开启事务
                    $pdo->beginTransaction();
    
                    // 删除主表记录
                    $deleteQun = $pdo->prepare("DELETE FROM huoma_qun WHERE qun_id = :qun_id");
                    $deleteQun->execute(['qun_id' => $qun_id]);
    
                    // 删除子码记录
                    $deleteZima = $pdo->prepare("DELETE FROM huoma_qun_zima WHERE qun_id = :qun_id");
                    $deleteZima->execute(['qun_id' => $qun_id]);
    
                    // 提交事务
                    $pdo->commit();
    
                    $result = ['code' => 200, 'msg' => '删除成功'];
    
                } else {
                    $result = ['code' => 202, 'msg' => '删除失败：数据不存在或无权限'];
                }
    
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $result = ['code' => 500, 'msg' => '数据库操作失败：' . $e->getMessage()];
            }
        }
    
    } else {
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>
