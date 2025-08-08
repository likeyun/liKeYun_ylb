<?php

    header("Content-type:application/json");
    
    session_start();
    
    if (isset($_SESSION["yinliubao"])) {
    
        // 获取参数
        $zm_id = trim($_GET['zm_id']);
    
        if (empty($zm_id)) {
            $result = ['code' => 203, 'msg' => '非法请求'];
        } else {
    
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
    
            // 引入数据库配置
            include '../Db.php';
    
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    
            try {
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
    
                // 获取 zm_id 所对应的 qun_id
                $stmt = $pdo->prepare("SELECT qun_id FROM huoma_qun_zima WHERE zm_id = :zm_id");
                $stmt->execute(['zm_id' => $zm_id]);
                $zimaInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$zimaInfo) {
                    $result = ['code' => 202, 'msg' => '删除失败：群二维码不存在'];
                } else {
                    $qun_id = $zimaInfo['qun_id'];
    
                    // 验证用户
                    $stmtQun = $pdo->prepare("SELECT qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
                    $stmtQun->execute(['qun_id' => $qun_id]);
                    $qunInfo = $stmtQun->fetch(PDO::FETCH_ASSOC);
    
                    if ($qunInfo && $qunInfo['qun_creat_user'] === $LoginUser) {
    
                        // 删除群二维码
                        $delStmt = $pdo->prepare("DELETE FROM huoma_qun_zima WHERE zm_id = :zm_id");
                        $delResult = $delStmt->execute(['zm_id' => $zm_id]);
    
                        if ($delResult) {
                            $result = ['code' => 200, 'msg' => '删除成功', 'qun_id' => $qun_id];
                        } else {
                            $result = ['code' => 202, 'msg' => '删除失败：执行失败'];
                        }
    
                    } else {
                        $result = ['code' => 202, 'msg' => '删除失败：无权限或数据不存在'];
                    }
                }
    
            } catch (PDOException $e) {
                $result = ['code' => 500, 'msg' => '数据库错误：' . $e->getMessage()];
            }
        }
    
    } else {
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    // 输出 JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>
