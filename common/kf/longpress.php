<?php

    // 获取 zm_id 参数
    $zm_id = isset($_GET['zm_id']) ? $_GET['zm_id'] : null;
    
    if ($zm_id === null) {
        exit('缺少 zm_id 参数');
    }
    
    try {
        // 连接数据库
        include '../../console/Db.php';
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 编写 SQL（注意：+1 不能用参数绑定）
        $sql = "UPDATE huoma_kf_zima SET longpress_num = longpress_num + 1 WHERE zm_id = :zm_id";
    
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':zm_id', $zm_id, PDO::PARAM_INT);
        $stmt->execute();
    
        if ($stmt->rowCount()) {
            echo "✅ 更新成功";
        } else {
            echo "⚠️ 未找到匹配的记录或数据未变";
        }
    
    } catch (PDOException $e) {
        echo "数据库错误：" . $e->getMessage();
    }