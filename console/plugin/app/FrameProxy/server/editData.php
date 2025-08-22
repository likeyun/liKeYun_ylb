<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        // 已登录
        $data_title     = trim($_POST['data_title'] ?? '');
        $data_dxccym    = trim($_POST['data_dxccym'] ?? '');
        $data_jumplink  = trim($_POST['data_jumplink'] ?? '');
        $data_mode      = trim($_POST['data_mode'] ?? '');
        $data_id        = trim($_POST['data_id'] ?? '');
        $LoginUser      = trim($_SESSION["yinliubao"]);
    
        // 过滤参数
        if(empty($data_title)){
            $result = ['code' => 203, 'msg' => '标题未填写'];
        }else if(empty($data_mode)){
            $result = ['code' => 203, 'msg' => '请选择模式'];
        }else if(empty($data_dxccym)){
            $result = ['code' => 203, 'msg' => '请选择对象存储域名'];
        }else if(empty($data_jumplink)){
            $result = ['code' => 203, 'msg' => '请填写跳转地址'];
        }else if(!is_url($data_jumplink)){
            $result = ['code' => 203, 'msg' => '跳转地址不是正确格式的URL'];
        }else if(empty($data_id)){
            $result = ['code' => 203, 'msg' => '非法请求~'];
        }else{
    
            // 数据库配置
            include '../../../../Db.php';
    
            try {
                // 构建PDO对象
                $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
                // 先查询该 data_id 的创建者
                $stmt = $pdo->prepare("SELECT data_create_user FROM ylbPlugin_wxdmQk WHERE data_id = :data_id LIMIT 1");
                $stmt->execute([':data_id' => $data_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if($row){
                    $data_create_user = $row['data_create_user'];
    
                    // 验证当前用户是否一致
                    if($data_create_user === $LoginUser){
    
                        // 更新数据
                        $sql = "UPDATE ylbPlugin_wxdmQk 
                                SET data_title = :data_title, 
                                    data_jumplink = :data_jumplink, 
                                    data_dxccym = :data_dxccym, 
                                    data_mode = :data_mode 
                                WHERE data_id = :data_id AND data_create_user = :data_create_user";
                        
                        $updateStmt = $pdo->prepare($sql);
                        $res = $updateStmt->execute([
                            ':data_title' => $data_title,
                            ':data_jumplink' => $data_jumplink,
                            ':data_dxccym' => $data_dxccym,
                            ':data_mode' => $data_mode,
                            ':data_id' => $data_id,
                            ':data_create_user' => $data_create_user
                        ]);
    
                        if($res){
                            $result = ['code' => 200, 'msg' => '已保存'];
                        }else{
                            $result = ['code' => 202, 'msg' => '更新失败'];
                        }
    
                    }else{
                        $result = ['code' => 202, 'msg' => '非法操作'];
                    }
    
                }else{
                    $result = ['code' => 404, 'msg' => '记录不存在'];
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
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>