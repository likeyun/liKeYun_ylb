<?php

    /**
     * 状态码说明
     * 200 操作成功
     * 作者：TANKING
     */
    
    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        $LoginUser = $_SESSION["yinliubao"];
    
        // 读取JSON文件内容
        $jsonFile = '../app.json';
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
    
        // 连接数据库
        include '../../../../Db.php';
        try {
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // 验证当前登录用户是否为管理员
            $stmt = $pdo->prepare("SELECT user_admin FROM huoma_user WHERE user_name = :username LIMIT 1");
            $stmt->execute([':username' => $LoginUser]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if(!$user || $user['user_admin'] == 2){
                $result = ['code' => 202, 'msg' => '卸载失败：没有管理权限！'];
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                exit;
            }
    
            // 获取安装状态
            $status = $data['install'] ?? 1;
    
            if($status == 2){
                // 已安装 → 删除表
                $pdo->exec("DROP TABLE IF EXISTS ylbPlugin_wxdmQk");
    
                // 修改JSON状态
                $data['install'] = 1;
                $data['install_time'] = "";
                $data['current_status'] = "未安装";
    
                // 写回JSON
                $appJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                file_put_contents($jsonFile, $appJsonData);
    
                $result = ['code' => 200, 'msg' => '已卸载'];
            }else{
                $result = ['code' => 201, 'msg' => '卸载失败'];
            }
    
        } catch (PDOException $e) {
            $result = ['code' => 500, 'msg' => '数据库错误: '.$e->getMessage()];
        }
    
    }else{
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>
