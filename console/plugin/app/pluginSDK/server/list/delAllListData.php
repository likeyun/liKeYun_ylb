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
                'msg' => '非法请求'
            );
        }else{
            
            // 获取当前登录用户
            $LoginUser = $_SESSION["yinliubao"];
            
            try {
                
                // 引入数据库配置文件
                include '../../../../../Db.php';
                
                // 创建 PDO 实例，建立数据库连接
                $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
                
                // 设置 PDO 错误模式为异常模式
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 验证用户
                $sql = "SELECT listdata_adduser FROM ylbPlugin_sdk_list WHERE data_id = :data_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':data_id' => $data_id]);
                $getCreatUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($getCreatUser && $getCreatUser['listdata_adduser'] == $LoginUser){
                    
                    // 用户一致，允许删除操作
                    // 准备删除的 SQL 语句
                    $sql = "DELETE FROM ylbPlugin_sdk_list WHERE data_id = :data_id";
                    $stmt = $pdo->prepare($sql);
                    $delResult = $stmt->execute([':data_id' => $data_id]);
                    
                    if($delResult){
                        
                        // 删除成功
                        $result = array(
                            'code' => 200,
                            'msg' => '已清空'
                        );
                    }else{
                        
                        // 删除失败
                        $result = array(
                            'code' => 202,
                            'msg' => '删除失败'
                        );
                    }
                }else{
                    
                    // 用户不一致或记录不存在
                    $result = array(
                        'code' => 202,
                        'msg' => '删除失败：禁止操作'
                    );
                }
                
            } catch(PDOException $e) {
                
                // 捕获数据库操作异常
                $result = array(
                    'code' => 202,
                    'msg' => '删除失败: ' . $e->getMessage()
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