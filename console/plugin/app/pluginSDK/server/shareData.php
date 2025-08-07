<?php

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 启动会话，用于检查用户登录状态
    session_start();
    
    // 判断用户是否已登录
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录，获取并清理 GET 请求中的参数
        $data_id = trim(intval($_GET['data_id'])); // 数据 ID，确保为整数
        
        // 参数验证，确保 data_id 不为空
        if(empty($data_id) || !isset($data_id)){
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else{
            try {
                
                // 引入数据库配置文件
                include '../../../../Db.php';
                
                // 创建 PDO 实例，建立数据库连接
                $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
                
                // 设置 PDO 错误模式为异常模式
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 查询数据详情
                $sql = "SELECT data_dlym, data_rkym, data_key FROM ylbPlugin_sdk WHERE data_id = :data_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':data_id' => $data_id]);
                $getDataInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // 检查查询结果
                if($getDataInfo){
                    
                    // 提取短链域名、入口域名和短网址 Key
                    $data_dlym = $getDataInfo['data_dlym']; // 短链域名
                    $data_rkym = $getDataInfo['data_rkym']; // 入口域名
                    $data_key = $getDataInfo['data_key']; // 短网址 Key
                    
                    // 拼接短链接
                    $shortUrl = $data_dlym . '/sdk/' . $data_key;
                    
                    // 拼接长链接
                    // 获取当前请求的 URI
                    $currentUri = $_SERVER["REQUEST_URI"];
                    
                    // 解析 URI 并构建根路径
                    $parsedUrl = parse_url($currentUri);
                    $pathComponents = explode('/', $parsedUrl['path']);
                    
                    // 去掉最后六个路径部分，获取根路径
                    $rootPathComponents = array_slice($pathComponents, 0, -6);
                    $rootPath = implode('/', $rootPathComponents);
                    
                    // 路径修改指引：
                    // 1. 如果不希望使用 common 这个目录，请自行修改下方的 common
                    // 2. 修改完成后，还需去引流宝根目录创建你自己的目录，并且将 common 里面的 sdkdata 整个目录复制到你新建的目录内
                    // 3. 还需要修改短网址调度器里面的目录，在 /s/sdkdata.php 里面修改，具体修改的位置里面有说
                    
                    // 构建完整的长链接 URL
                    $longUrl = $data_rkym . $rootPath . '/common/sdkdata/rkpage/?pid=' . base64_encode($data_id) . '&dkey=' . $data_key;
                    
                    // 返回成功结果
                    $result = array(
                        'code' => 200,
                        'msg' => '获取成功',
                        'shortUrl' => $shortUrl,
                        'longUrl' => $longUrl . '#landpage',
                        'qrcodeUrl' => $longUrl . '#qrcode'
                    );
                }else{
                    
                    // 无查询结果
                    $result = array(
                        'code' => 204,
                        'msg' => '获取失败'
                    );
                }
                
            } catch(PDOException $e) {
                
                // 捕获数据库操作异常
                $result = array(
                    'code' => 202,
                    'msg' => '获取失败: ' . $e->getMessage()
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