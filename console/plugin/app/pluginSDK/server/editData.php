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
        $data_id = trim($_POST['data_id']); // 数据 ID
        
        // 处理过期时间格式（前端格式为 2025-05-23T14:54，需转换为 Y-m-d H:i:s）
        $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $data_expire_time);
        $data_expire_time_formattedTime = $dateTime->format('Y-m-d H:i:s');
        
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
        }else if(empty($data_id) || !isset($data_id)){
            $result = array(
                'code' => 203,
                'msg' => '非法请求~'
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
                $sql = "SELECT data_create_user FROM ylbPlugin_sdk WHERE data_id = :data_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':data_id' => $data_id]);
                $getCreatUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($getCreatUser && $getCreatUser['data_create_user'] == $LoginUser){
                    
                    // 用户一致，允许更新操作
                    // 准备更新的 SQL 语句
                    $sql = "UPDATE ylbPlugin_sdk SET 
                            data_title = :data_title,
                            data_jumplink = :data_jumplink,
                            data_limit = :data_limit,
                            data_pic = :data_pic,
                            data_dlym = :data_dlym,
                            data_rkym = :data_rkym,
                            data_ldym = :data_ldym,
                            data_expire_time = :data_expire_time
                            WHERE data_id = :data_id AND data_create_user = :data_create_user";
                    
                    // 准备并执行更新
                    $stmt = $pdo->prepare($sql);
                    $updateResult = $stmt->execute([
                        ':data_title' => $data_title,
                        ':data_jumplink' => $data_jumplink,
                        ':data_limit' => $data_limit,
                        ':data_pic' => $data_pic,
                        ':data_dlym' => $data_dlym,
                        ':data_rkym' => $data_rkym,
                        ':data_ldym' => $data_ldym,
                        ':data_expire_time' => $data_expire_time_formattedTime,
                        ':data_id' => $data_id,
                        ':data_create_user' => $data_create_user
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