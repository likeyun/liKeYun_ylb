<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        // 已登录
        $data_id = isset($_GET['data_id']) ? intval($_GET['data_id']) : 0;
    
        if($data_id <= 0){
            // 非法请求
            $result = ['code' => 203, 'msg' => '非法请求'];
        }else{
            // 数据库配置
            include '../../../../Db.php';
    
            try {
                // 建立PDO连接
                $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
                // 查询详情
                $stmt = $pdo->prepare("SELECT data_dxccym, data_key FROM ylbPlugin_wxdmQk WHERE data_id = :data_id LIMIT 1");
                $stmt->execute([':data_id' => $data_id]);
                $getDataInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if($getDataInfo){
                    $data_dxccym = $getDataInfo['data_dxccym'];
                    $data_key = $getDataInfo['data_key'];
    
                    // 判断对象存储域名的类型，拼接参数
                    if (preg_match('/\.(html|htm|svg|xhtml|xhtm|xml|png|jpg|jpeg|bmp|shtml)$/i', $data_dxccym)) {
                        
                        // 如果以文件后缀结尾 → 用 ? 拼接参数
                        $shareUrl = $data_dxccym . '?key=' . $data_key;
                    } else if(preg_match('/\/\?$/', $data_dxccym)) {
                       
                        // 以 /? 结尾
                        $shareUrl = $data_dxccym . 'key=' . $data_key;
                    } else if(substr($data_dxccym, -1) === '?') {
                       
                        // 以 ? 结尾
                        $shareUrl = $data_dxccym . 'key=' . $data_key;
                    } else {
                        
                        // 其他情况 → 用 & 拼接参数
                        $shareUrl = $data_dxccym . '&key=' . $data_key;
                    }
    
                    $result = [
                        'code' => 200,
                        'msg' => '获取成功',
                        'shareUrl' => $shareUrl . '#wechat_redirect',
                        'qrcodeUrl' => $shareUrl . '#qrcode'
                    ];
                }else{
                    $result = ['code' => 204, 'msg' => '获取失败'];
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
