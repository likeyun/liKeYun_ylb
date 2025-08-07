<?php

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 启动会话，用于检查用户登录状态
    session_start();
    
    // 判断用户是否已登录
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录，获取并清理 POST 请求中的参数
        $data_id = trim($_POST['data_id']);
        $listdata_id = trim($_POST['listdata_id']);
        $listdata_1 = trim($_POST['listdata_1']);
        $listdata_2 = trim($_POST['listdata_2']);
        $listdata_3 = trim($_POST['listdata_3']);
        $listdata_4 = trim($_POST['listdata_4']);
        $listdata_status = trim($_POST['listdata_status']);
        
        // 参数验证，确保必填字段不为空
        if(empty($listdata_1) || !isset($listdata_1)){
            $result = array(
                'code' => 203,
                'msg' => '字段1未填写'
            );
        }else if(empty($listdata_2) || !isset($listdata_2)){
            $result = array(
                'code' => 203,
                'msg' => '字段2未填写'
            );
        }else if(empty($listdata_4) || !isset($listdata_4)){
            $result = array(
                'code' => 203,
                'msg' => '字段4未选择'
            );
        }else if(empty($listdata_3) || !isset($listdata_3)){
            $result = array(
                'code' => 203,
                'msg' => '字段3未填写'
            );
        }else if(empty($listdata_status) || !isset($listdata_status)){
            $result = array(
                'code' => 203,
                'msg' => '状态未选择'
            );
        }else if(empty($data_id) || !isset($data_id)){
            $result = array(
                'code' => 203,
                'msg' => '服务错误：data_id 为空'
            );
        }else if(empty($listdata_id) || !isset($listdata_id)){
            $result = array(
                'code' => 203,
                'msg' => '服务错误：listdata_id 为空'
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
                $sql = "SELECT listdata_adduser FROM ylbPlugin_sdk_list WHERE listdata_id = :listdata_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':listdata_id' => $listdata_id]);
                $getCreatUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($getCreatUser && $getCreatUser['listdata_adduser'] == $LoginUser){
                    
                    // 用户一致，允许更新操作
                    // 准备更新的 SQL 语句
                    $sql = "UPDATE ylbPlugin_sdk_list SET 
                            listdata_1 = :listdata_1,
                            listdata_2 = :listdata_2,
                            listdata_3 = :listdata_3,
                            listdata_4 = :listdata_4,
                            listdata_status = :listdata_status
                            WHERE listdata_id = :listdata_id AND listdata_adduser = :listdata_adduser";
                    
                    // 准备并执行更新
                    $stmt = $pdo->prepare($sql);
                    $updateResult = $stmt->execute([
                        ':listdata_1' => $listdata_1,
                        ':listdata_2' => $listdata_2,
                        ':listdata_3' => $listdata_3,
                        ':listdata_4' => $listdata_4,
                        ':listdata_status' => $listdata_status,
                        ':listdata_id' => $listdata_id,
                        ':listdata_adduser' => $LoginUser
                    ]);
                    
                    if($updateResult){
                        
                        // 更新成功
                        $result = array(
                            'code' => 200,
                            'msg' => '已保存'
                        );
                    }else{
                        
                        // 更新失败
                        $result = array(
                            'code' => 202,
                            'msg' => '更新失败'
                        );
                    }
                }else{
                    
                    // 用户不一致或记录不存在
                    $result = array(
                        'code' => 202,
                        'msg' => '非法操作'
                    );
                }
                
            } catch(PDOException $e) {
                
                // 捕获数据库操作异常
                $result = array(
                    'code' => 202,
                    'msg' => '更新失败: ' . $e->getMessage()
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
    
    // 验证URL真实性
    function isValidURL($url) {
        
        // 使用filter_var验证URL格式
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false; // 格式不正确
        }
    
        // 检查URL是否以http://或https://开头
        if (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0) {
            return true; // 格式正确且以http或https开头
        }
        return false; // 不是以http或https开头
    }
    
    // 输出 JSON 格式的响应结果，支持中文不转码
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>