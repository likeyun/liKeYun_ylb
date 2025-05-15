<?php

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 启动会话，用于检查用户登录状态
    session_start();
    
    // 判断用户是否已登录
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录，获取并清理 GET 请求中的参数
        $data_id = trim($_GET['data_id']); // 数据 ID
        
        // 参数验证，确保 data_id 不为空
        if(empty($data_id) || !isset($data_id)){
            $result = array(
                'code' => 203,
                'msg' => '非法请求？？'
            );
        }else{
            
            // 获取当前登录用户
            $LoginUser = $_SESSION["yinliubao"];
            
            try {
                // 引入数据库配置文件
                include '../../../../Db.php';
                
                // 创建 PDO 实例，建立数据库连接
                $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
                
                // 设置 PDO 错误模式为异常模式
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 验证当前 data_id 的创建者是否为当前登录用户
                $sql = "SELECT data_create_user, data_status FROM ylbPlugin_sdk WHERE data_id = :data_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':data_id' => $data_id]);
                $checkUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // 检查查询结果及用户权限
                if($checkUser && $checkUser['data_create_user'] == $LoginUser){
                    
                    // 用户一致，允许操作
                    // 获取当前状态并决定更新后的状态
                    $data_status = $checkUser['data_status'];
                    if($data_status == 1){
                        $updateData = ['data_status' => 2];
                        $statusText = '已停用';
                    }else{
                        $updateData = ['data_status' => 1];
                        $statusText = '已启用';
                    }
                    
                    // 准备更新状态的 SQL 语句
                    $sql = "UPDATE ylbPlugin_sdk SET data_status = :data_status WHERE data_id = :data_id AND data_create_user = :data_create_user";
                    $stmt = $pdo->prepare($sql);
                    $updateResult = $stmt->execute([
                        ':data_status' => $updateData['data_status'],
                        ':data_id' => $data_id,
                        ':data_create_user' => $LoginUser
                    ]);
                    
                    // 检查更新结果
                    if($updateResult){
                        
                        // 操作成功
                        $result = array(
                            'code' => 200,
                            'msg' => $statusText
                        );
                    }else{
                        
                        // 操作失败
                        $result = array(
                            'code' => 202,
                            'msg' => '操作失败'
                        );
                    }
                }else{
                    
                    // 用户不一致或记录不存在
                    $result = array(
                        'code' => 202,
                        'msg' => '非法请求，鉴权失败！'
                    );
                }
                
            } catch(PDOException $e) {
                
                // 捕获数据库操作异常
                $result = array(
                    'code' => 202,
                    'msg' => '操作失败: ' . $e->getMessage()
                );
            }
        }
    }else{
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
    
    // 输出 JSON 格式的响应结果，支持中文不转码
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>