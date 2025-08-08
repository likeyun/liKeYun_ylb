<?php

    // 页面编码
    header("Content-type: application/json");
    
    // 启动 session
    session_start();
    
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode([
            'code' => 201,
            'msg'  => '未登录'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 当前登录用户
    $LoginUser = $_SESSION["yinliubao"];
    
    // 当前页数
    $page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
    
    // 每页条数
    $pageSize = 8;
    $offset = ($page - 1) * $pageSize;
    
    // 引入数据库配置
    include '../Db.php';
    
    try {
        // 创建 PDO 实例
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 获取总数
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM ylb_qun_bingliu WHERE createUser = :createUser");
        $countStmt->execute([':createUser' => $LoginUser]);
        $bingliuNum = $countStmt->fetchColumn();
    
        // 总页数
        $allpage = ceil($bingliuNum / $pageSize);
    
        // 上一页、下一页
        $prepage = max(1, $page - 1);
        $nextpage = min($allpage, $page + 1);
    
        // 获取数据
        $stmt = $pdo->prepare("SELECT * FROM ylb_qun_bingliu WHERE createUser = :createUser ORDER BY ID DESC LIMIT :offset, :limit");
        $stmt->bindValue(':createUser', $LoginUser, PDO::PARAM_STR);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
        $stmt->execute();
        $bingliuList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if ($bingliuList) {
            echo json_encode([
                'code'         => 200,
                'msg'          => '获取成功',
                'bingliuList'  => $bingliuList,
                'bingliuNum'   => $bingliuNum,
                'prepage'      => $prepage,
                'nextpage'     => $nextpage,
                'allpage'      => $allpage,
                'page'         => $page
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => 204,
                'msg'  => '暂无并流任务'
            ], JSON_UNESCAPED_UNICODE);
        }
    
    } catch (PDOException $e) {
        echo json_encode([
            'code' => 500,
            'msg'  => '数据库错误：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
