<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        // 已登录
        $data_id = trim($_GET['data_id'] ?? '');
        $LoginUser = $_SESSION["yinliubao"];
    
        // 过滤参数
        if(empty($data_id)){
            $result = ['code' => 203, 'msg' => '非法请求'];
        }else{
    
            // 数据库配置
            include '../../../../Db.php';
    
            try {
                // 建立PDO连接
                $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
                // 验证用户
                $stmt = $pdo->prepare("SELECT data_create_user FROM ylbPlugin_wxdmQk WHERE data_id = :data_id LIMIT 1");
                $stmt->execute([':data_id' => $data_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if($row){
                    $data_create_user = $row['data_create_user'];
    
                    // 判断是否为当前登录用户
                    if($data_create_user === $LoginUser){
    
                        // 删除操作
                        $delStmt = $pdo->prepare("DELETE FROM ylbPlugin_wxdmQk WHERE data_id = :data_id AND data_create_user = :data_create_user");
                        $res = $delStmt->execute([
                            ':data_id' => $data_id,
                            ':data_create_user' => $LoginUser
                        ]);
    
                        if($res){
                            $result = ['code' => 200, 'msg' => '已删除'];
                        }else{
                            $result = ['code' => 202, 'msg' => '删除失败'];
                        }
    
                    }else{
                        $result = ['code' => 202, 'msg' => '删除失败：禁止操作'];
                    }
    
                }else{
                    $result = ['code' => 404, 'msg' => '记录不存在'];
                }
    
            } catch (PDOException $e) {
                $result = ['code' => 500, 'msg' => '数据库错误: '.$e->getMessage()];
            }
        }
    
    }else{
        
        // 未登录
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>
