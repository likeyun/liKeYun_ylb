<?php
    
    // 字符编码
    header('Content-Type: application/javascript; charset=utf-8');

    // 接收参数
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $data_key = isset($_GET['key']) ? $_GET['key'] : '';

    // 参数过滤
    if (empty($callback) || empty($data_key)) {
        echo $callback . '('.json_encode([
            "code" => 1,
            "msg" => "缺少参数",
            "data_jumplink" => ""
        ]).');';
        exit;
    }

    // 引入配置
    include '../../console/Db.php';

    // 数据库连接
    try {
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // 查询数据
        $stmt = $pdo->prepare("
            SELECT data_jumplink, data_status, data_title, data_pv 
            FROM ylbPlugin_wxdmQk 
            WHERE data_key = :data_key 
            LIMIT 1
        ");
        $stmt->bindParam(':data_key', $data_key, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();

        // 结果判断
        if ($result) {
            if ($result['data_status'] == 2) {

                // 停用
                $response = ["code" => -1, "msg" => "当前链接已被管理员停用"];
            } else {

                // 成功获取，更新访问量
                $pdo->prepare("
                    UPDATE ylbPlugin_wxdmQk 
                    SET data_pv = data_pv + 1 
                    WHERE data_key = :data_key
                ")->execute([':data_key' => $data_key]);

                // 返回数据
                $response = [
                    "code" => 0,
                    "msg" => "成功",
                    "data_jumplink" => $result['data_jumplink'],
                    "data_title" => $result['data_title'],
                    "data_pv" => $result['data_pv'] + 1
                ];
            }
        } else {

            // 链接不存在
            $response = ["code" => 1, "msg" => "链接不存在或已被管理员删除", "data_jumplink" => ""];
        }
    } catch (PDOException $e) {

        // 数据库错误
        $response = ["code" => 1, "msg" => "数据库错误: " . $e->getMessage(), "data_jumplink" => ""];
    }

    // 返回 JSONP
    echo $callback . '(' . json_encode($response, JSON_UNESCAPED_UNICODE) . ');';

?>
