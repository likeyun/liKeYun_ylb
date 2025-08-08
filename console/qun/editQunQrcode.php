<?php

    // 设置响应类型
    header("Content-type:application/json");
    
    // 启动 session
    session_start();
    
    if (isset($_SESSION["yinliubao"])) {
    
        // 接收参数
        $zm_yz = trim($_POST['zm_yz']);
        $zm_leader = trim($_POST['zm_leader']);
        $zm_qrcode = trim($_POST['zm_qrcode']);
        $zm_id = trim($_POST['zm_id']);
    
        // 参数校验
        if (empty($zm_yz) || $zm_yz == 0) {
            $result = ['code' => 203, 'msg' => '请设置阈值'];
        } else if (empty($zm_qrcode)) {
            $result = ['code' => 203, 'msg' => '请上传群二维码'];
        } else if (empty($zm_id)) {
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
    
                // 获取 zm_id 对应的 qun_id
                $stmtZima = $pdo->prepare("SELECT qun_id FROM huoma_qun_zima WHERE zm_id = :zm_id");
                $stmtZima->execute(['zm_id' => $zm_id]);
                $zima = $stmtZima->fetch(PDO::FETCH_ASSOC);
    
                if ($zima) {
                    $qun_id = $zima['qun_id'];
    
                    // 获取 qun_id 对应的 qun_creat_user
                    $stmtQun = $pdo->prepare("SELECT qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
                    $stmtQun->execute(['qun_id' => $qun_id]);
                    $qun = $stmtQun->fetch(PDO::FETCH_ASSOC);
    
                    if ($qun && $qun['qun_creat_user'] === $LoginUser) {
                        
                        // 用户一致，允许更新
                        $zm_update_time = date('Y-m-d H:i:s');
                        $updateStmt = $pdo->prepare("
                            UPDATE huoma_qun_zima SET 
                                zm_yz = :zm_yz,
                                zm_leader = :zm_leader,
                                zm_qrcode = :zm_qrcode,
                                zm_update_time = :zm_update_time
                            WHERE zm_id = :zm_id
                        ");
    
                        $success = $updateStmt->execute([
                            'zm_yz' => $zm_yz,
                            'zm_leader' => $zm_leader,
                            'zm_qrcode' => $zm_qrcode,
                            'zm_update_time' => $zm_update_time,
                            'zm_id' => $zm_id
                        ]);
    
                        if ($success) {
                            $result = ['code' => 200, 'msg' => '更新成功', 'qun_id' => $qun_id];
                        } else {
                            $result = ['code' => 202, 'msg' => '更新失败'];
                        }
                    } else {
                        $result = ['code' => 202, 'msg' => '更新失败：无权限或不存在'];
                    }
    
                } else {
                    $result = ['code' => 202, 'msg' => '找不到子码信息'];
                }
    
            } catch (PDOException $e) {
                $result = ['code' => 500, 'msg' => '数据库操作异常：' . $e->getMessage()];
            }
        }
    
    } else {
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
