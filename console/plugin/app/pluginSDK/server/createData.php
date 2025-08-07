<?php

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 启动会话，用于检查用户登录状态
    session_start();
    
    // 判断用户是否已登录
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录，获取并清理 POST 请求中的参数
        $data_title = trim($_POST['data_title']); // 数据标题
        $data_limit = trim($_POST['data_limit']); // 数据限制
        $data_expire_time = trim($_POST['data_expire_time']); // 过期时间
        $data_dlym = trim($_POST['data_dlym']); // 短链域名
        $data_rkym = trim($_POST['data_rkym']); // 入口域名
        $data_ldym = trim($_POST['data_ldym']); // 落地域名
        $data_pic = trim($_POST['data_pic']); // 图片地址
        $data_jumplink = trim($_POST['data_jumplink']); // 跳转链接
        $data_create_user = trim($_SESSION["yinliubao"]); // 创建用户（从会话获取）
        
        // 格式化过期时间为数据库所需的格式（Y-m-d H:i:s）
        $data_expire_time_format = new DateTime($data_expire_time);
        $data_expire_time_formatted = $data_expire_time_format->format("Y-m-d H:i:s");
        
        // 参数验证，确保必填字段不为空
        if(empty($data_title) || !isset($data_title)){
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($data_pic) || !isset($data_pic)){
            $result = array(
                'code' => 203,
                'msg' => '请上传图片'
            );
        }else if($data_pic && !isValidURL($data_pic)){
            $result = array(
                'code' => 203,
                'msg' => '你上传的图片不是一个真实的图片地址'
            );
        }else if(empty($data_dlym) || empty($data_rkym) || empty($data_ldym)){
            $result = array(
                'code' => 203,
                'msg' => '还有域名未选择'
            );
        }else if(empty($data_jumplink) || !isset($data_jumplink)){
            $result = array(
                'code' => 203,
                'msg' => '请填写跳转地址'
            );
        }else if($data_jumplink && !isValidURL($data_jumplink)){
            $result = array(
                'code' => 203,
                'msg' => '跳转地址可能不是一个真实的URL'
            );
        }else{
            
            // 生成唯一的ID，格式为“10”开头加随机数
            $data_id = '10'.rand(101112,989898);
            
            try {
                
                // 数据库连接配置（需替换为实际值）
                include '../../../../Db.php';
                
                // 创建 PDO 实例，建立数据库连接
                $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
                
                // 设置 PDO 错误模式为异常模式
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 准备插入数据的 SQL 语句，使用命名占位符
                $sql = "INSERT INTO ylbPlugin_sdk (
                    data_id, 
                    data_title, 
                    data_key, 
                    data_jumplink, 
                    data_limit, 
                    data_pic, 
                    data_dlym, 
                    data_rkym, 
                    data_ldym, 
                    data_expire_time, 
                    data_create_user
                ) VALUES (
                    :data_id, 
                    :data_title, 
                    :data_key, 
                    :data_jumplink, 
                    :data_limit, 
                    :data_pic, 
                    :data_dlym, 
                    :data_rkym, 
                    :data_ldym, 
                    :data_expire_time, 
                    :data_create_user
                )";
                
                // 准备并执行 SQL 语句，绑定参数
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':data_id' => $data_id,
                    ':data_title' => $data_title,
                    ':data_key' => createKey(5), // 生成 5 位随机密钥
                    ':data_jumplink' => $data_jumplink,
                    ':data_limit' => $data_limit,
                    ':data_pic' => $data_pic,
                    ':data_dlym' => $data_dlym,
                    ':data_rkym' => $data_rkym,
                    ':data_ldym' => $data_ldym,
                    ':data_expire_time' => $data_expire_time_formatted,
                    ':data_create_user' => $data_create_user
                ]);
                
                // 插入成功，返回成功响应
                $result = array(
                    'code' => 200,
                    'msg' => '创建成功'
                );
                
            } catch(PDOException $e) {
                
                // 捕获数据库操作异常，返回失败响应
                $result = array(
                    'code' => 202,
                    'msg' => '创建失败: ' . $e->getMessage()
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