<?php

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 启动会话，用于检查用户登录状态
    session_start();
    
    // 判断用户是否已登录
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录，获取并清理 POST 请求中的参数
        $data_id = trim($_POST['data_id']);
        $listdata_1 = trim($_POST['listdata_1']);
        $listdata_2 = trim($_POST['listdata_2']);
        $listdata_3 = trim($_POST['listdata_3']);
        $listdata_4 = trim($_POST['listdata_4']);
        $listdata_adduser = trim($_SESSION["yinliubao"]);
        
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
        }else if(empty($data_id) || !isset($data_id)){
            $result = array(
                'code' => 203,
                'msg' => '服务错误：data_id 为空'
            );
        }else{
            
            // 生成唯一的ID，格式为“10”开头加随机数
            $listdata_id = '10'.rand(100000,999999);
            
            try {
                
                // 数据库连接配置
                include '../../../../../Db.php';
                
                // 创建 PDO 实例，建立数据库连接
                $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
                
                // 设置 PDO 错误模式为异常模式
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 准备插入数据的 SQL 语句，使用命名占位符
                $sql = "INSERT INTO ylbPlugin_sdk_list (
                    data_id, 
                    listdata_id, 
                    listdata_1, 
                    listdata_2, 
                    listdata_3, 
                    listdata_4, 
                    listdata_adduser
                ) VALUES (
                    :data_id, 
                    :listdata_id, 
                    :listdata_1, 
                    :listdata_2, 
                    :listdata_3, 
                    :listdata_4, 
                    :listdata_adduser
                )";
                
                // 准备并执行 SQL 语句，绑定参数
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':data_id' => $data_id,
                    ':listdata_id' => $listdata_id,
                    ':listdata_1' => $listdata_1,
                    ':listdata_2' => $listdata_2,
                    ':listdata_3' => $listdata_3,
                    ':listdata_4' => $listdata_4,
                    ':listdata_adduser' => $listdata_adduser
                ]);
                
                // 插入成功，返回成功响应
                $result = array(
                    'code' => 200,
                    'msg' => '添加成功'
                );
                
            } catch(PDOException $e) {
                
                // 捕获数据库操作异常，返回失败响应
                $result = array(
                    'code' => 202,
                    'msg' => '添加失败: ' . $e->getMessage()
                );
            }
        }
    }else{
        
        // 未登录，返回未登录提示
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
    
    // 生成指定长度的随机字符串
    function createKey($length){
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'; // 字符池
        $randStr = str_shuffle($str); // 随机打乱字符
        $rands = substr($randStr, 0, $length); // 截取指定长度
        return $rands;
    }
    
    // 输出 JSON 格式的响应结果，支持中文不转码
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>