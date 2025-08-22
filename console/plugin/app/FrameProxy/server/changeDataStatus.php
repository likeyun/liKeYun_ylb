<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        // 已登录
        $data_id = isset($_GET['data_id']) ? trim($_GET['data_id']) : '';
    
        if(empty($data_id)){
            $result = ['code' => 203, 'msg' => '非法请求'];
        }else{
            $LoginUser = $_SESSION["yinliubao"];
    
            // 数据库配置
            include '../../../../Db.php';
    
            try {
                // 建立PDO连接
                $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
                // 查询当前状态及创建者
                $stmt = $pdo->prepare("SELECT data_create_user, data_status FROM ylbPlugin_wxdmQk WHERE data_id = :data_id LIMIT 1");
                $stmt->execute([':data_id' => $data_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if($row){
                    if($row['data_create_user'] !== $LoginUser){
                        $result = ['code' => 202, 'msg' => '非法请求，鉴权失败！'];
                    }else{
                        // 切换状态
                        $new_status = ($row['data_status'] == 1) ? 2 : 1;
                        $statusText = ($new_status == 1) ? '已启用' : '已停用';
    
                        $updateStmt = $pdo->prepare("UPDATE ylbPlugin_wxdmQk 
                                                     SET data_status = :new_status 
                                                     WHERE data_id = :data_id AND data_create_user = :user");
                        $res = $updateStmt->execute([
                            ':new_status' => $new_status,
                            ':data_id' => $data_id,
                            ':user' => $LoginUser
                        ]);
    
                        if($res){
                            $result = ['code' => 200, 'msg' => $statusText];
                        }else{
                            $result = ['code' => 202, 'msg' => '操作失败'];
                        }
                    }
    
                }else{
                    $result = ['code' => 404, 'msg' => '记录不存在'];
                }
    
            } catch (PDOException $e) {
                $result = ['code' => 500, 'msg' => '数据库错误: '.$e->getMessage()];
            }
        }
    
    }else{
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>
