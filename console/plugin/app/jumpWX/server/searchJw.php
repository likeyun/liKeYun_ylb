<?php

    // 返回JSON
    header('Content-Type: application/json');
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])) {
        
        // 已登录
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库连接
        include '../../../../Db.php';
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 查询当前登录用户的 user_admin 字段
        $sql_user = "SELECT user_admin FROM huoma_user WHERE user_name = :username";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->bindParam(':username', $LoginUser, PDO::PARAM_STR);
        $stmt_user->execute();
        $userInfo = $stmt_user->fetch(PDO::FETCH_ASSOC);
        $user_admin = $userInfo['user_admin'];
        
        if((int)$user_admin !== 1 || $user_admin !== '1') {
            
            // 非超管
            echo json_encode([
                'code' => 206,
                'msg' => '无查询权限'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 获取关键词
        $keyword = trim($_GET['keyword']) ?? '';
        
        if ($keyword !== '') {
            
            // 构建 SQL 查询
            $sql = "
                SELECT * FROM ylb_jumpWX
                WHERE jw_id LIKE :kw
                   OR jw_key LIKE :kw
                   OR jw_title LIKE :kw
                   OR jw_create_user LIKE :kw
            ";
            
            $stmt = $pdo->prepare($sql);
            $likeKeyword = "%$keyword%";
            $stmt->bindParam(':kw', $likeKeyword, PDO::PARAM_STR);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($results) > 0) {
                
                // 有数据
                echo json_encode([
                    'code' => 200,
                    'jwList' => $results,
                    'msg' => '查询成功'
                ], JSON_UNESCAPED_UNICODE);
            }else {
                
                // 无数据
                echo json_encode([
                    'code' => 201,
                    'msg' => '暂无相关数据'
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            
            // 关键词为空
            echo json_encode([
                'code' => 202,
                'msg' => '关键词不能为空'
            ], JSON_UNESCAPED_UNICODE);
        }
    }else {
        
        // 关键词为空
        echo json_encode([
            'code' => 204,
            'msg' => '未登录'
        ], JSON_UNESCAPED_UNICODE);
    }

?>
