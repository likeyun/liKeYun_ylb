<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        // 已登录
        $data_title = trim($_POST['data_title'] ?? '');
        $data_dxccym = trim($_POST['data_dxccym'] ?? '');
        $data_mode = trim($_POST['data_mode'] ?? '');
        $data_jumplink = trim($_POST['data_jumplink'] ?? '');
        $data_create_user = trim($_SESSION["yinliubao"]);
    
        // 过滤参数
        if(empty($data_title)){
            $result = ['code' => 203, 'msg' => '标题未填写'];
        }else if(empty($data_dxccym)){
            $result = ['code' => 203, 'msg' => '请选择对象存储域名'];
        }else if(empty($data_mode)){
            $result = ['code' => 203, 'msg' => '请选择模式'];
        }else if(empty($data_jumplink)){
            $result = ['code' => 203, 'msg' => '请填写跳转地址'];
        }else if(!is_url($data_jumplink)){
            $result = ['code' => 203, 'msg' => '跳转地址不是正确格式的URL'];
        }else{
            
            
            // 数据库配置
            include '../../../../Db.php';
    
            // ID生成
            $data_id = '10'.rand(100000,999999);
    
            // 生成Key
            $data_key = createKey(5);
    
            try {
                
                // 建立PDO连接
                $db_host = $config['db_host'];
                $db_name = $config['db_name'];
                $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
                // SQL
                $sql = "INSERT INTO ylbPlugin_wxdmQk 
                        (data_id, data_title, data_key, data_mode, data_jumplink, data_dxccym, data_create_user) 
                        VALUES 
                        (:data_id, :data_title, :data_key, :data_mode, :data_jumplink, :data_dxccym, :data_create_user)";
                
                // 预处理
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([
                    ':data_id' => $data_id,
                    ':data_title' => $data_title,
                    ':data_key' => $data_key,
                    ':data_mode' => $data_mode,
                    ':data_jumplink' => $data_jumplink,
                    ':data_dxccym' => $data_dxccym,
                    ':data_create_user' => $data_create_user
                ]);
    
                if($res){
                    $result = ['code' => 200, 'msg' => '创建成功'];
                }else{
                    $result = ['code' => 202, 'msg' => '创建失败'];
                }
    
            } catch (PDOException $e) {
                $result = ['code' => 500, 'msg' => '数据库错误: '.$e->getMessage()];
            }
        }
    
    }else{
        // 未登录
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    function is_url($url) {
        $r = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
        if(preg_match($r,$url)){
            return true;
        }else{
            return false;
        }
    }
    
    // 生成Key
    function createKey($length){
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);
        return substr($randStr,0,$length);
    }
    
    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>