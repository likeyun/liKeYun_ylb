<?php

    // 设置响应类型
    header("Content-type:application/json");
    
    // 启动 session
    session_start();
    
    if (isset($_SESSION["yinliubao"])) {
    
        // 接收并过滤参数
        $qun_title = trim(htmlspecialchars($_POST['qun_title']));
        $qun_beizhu = trim($_POST['qun_beizhu']);
        $qun_rkym = trim($_POST['qun_rkym']);
        $qun_ldym = trim($_POST['qun_ldym']);
        $qun_dlym = trim($_POST['qun_dlym']);
        $qun_kf_status = trim($_POST['qun_kf_status']);
        $qun_kf = trim($_POST['qun_kf']);
        $qun_safety = trim($_POST['qun_safety']);
        $qun_id = trim($_POST['qun_id']);
        $qun_notify = trim($_POST['qun_notify']);
    
        // 参数校验
        if (empty($qun_title)) {
            $result = ['code' => 203, 'msg' => '标题未填写'];
        } else if (empty($qun_rkym)) {
            $result = ['code' => 203, 'msg' => '入口域名未选择'];
        } else if (empty($qun_ldym)) {
            $result = ['code' => 203, 'msg' => '落地域名未选择'];
        } else if (empty($qun_dlym)) {
            $result = ['code' => 203, 'msg' => '短链域名未选择'];
        } else if (empty($qun_kf_status)) {
            $result = ['code' => 203, 'msg' => '客服显示状态未设置'];
        } else if (empty($qun_kf) && $qun_kf_status == '1') {
            $result = ['code' => 203, 'msg' => '客服二维码未上传'];
        } else if (empty($qun_safety)) {
            $result = ['code' => 203, 'msg' => '顶部扫码安全提示未设置'];
        } else if (empty($qun_id)) {
            $result = ['code' => 203, 'msg' => '非法请求'];
        } else {
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        	
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    
            try {
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
    
                // 查询发布者是否是当前登录用户
                $stmt = $pdo->prepare("SELECT qun_creat_user FROM huoma_qun WHERE qun_id = :qun_id");
                $stmt->execute(['qun_id' => $qun_id]);
                $qun = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($qun && $qun['qun_creat_user'] === $LoginUser) {
                    
                    // 更新数据
                    $updateStmt = $pdo->prepare("
                        UPDATE huoma_qun SET 
                            qun_title = :qun_title,
                            qun_beizhu = :qun_beizhu,
                            qun_rkym = :qun_rkym,
                            qun_ldym = :qun_ldym,
                            qun_dlym = :qun_dlym,
                            qun_kf_status = :qun_kf_status,
                            qun_kf = :qun_kf,
                            qun_notify = :qun_notify,
                            qun_safety = :qun_safety
                        WHERE 
                            qun_id = :qun_id AND 
                            qun_creat_user = :qun_creat_user
                    ");
                    $success = $updateStmt->execute([
                        'qun_title' => $qun_title,
                        'qun_beizhu' => $qun_beizhu,
                        'qun_rkym' => $qun_rkym,
                        'qun_ldym' => $qun_ldym,
                        'qun_dlym' => $qun_dlym,
                        'qun_kf_status' => $qun_kf_status,
                        'qun_kf' => $qun_kf,
                        'qun_notify' => $qun_notify,
                        'qun_safety' => $qun_safety,
                        'qun_id' => $qun_id,
                        'qun_creat_user' => $LoginUser
                    ]);
    
                    if ($success) {
                        $result = ['code' => 200, 'msg' => '已保存'];
                    } else {
                        $result = ['code' => 202, 'msg' => '保存失败'];
                    }
    
                } else {
                    $result = ['code' => 202, 'msg' => '非法请求'];
                }
    
            } catch (PDOException $e) {
                $result = ['code' => 500, 'msg' => '数据库连接失败：' . $e->getMessage()];
            }
        }
    
    } else {
        // 未登录
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    // 输出 JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
