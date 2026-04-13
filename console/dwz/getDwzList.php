<?php
header("Content-type:application/json;charset=utf-8");

session_start();
if(isset($_SESSION["yinliubao"])){

    $page = isset($_GET['p']) && intval($_GET['p']) > 0 ? intval($_GET['p']) : 1;
    $LoginUser = $_SESSION["yinliubao"];

    // 引入配置
    include '../Db.php';

    try {
        // 用配置创建PDO
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";

        $pdo = new PDO(
            $dsn,
            $config['db_user'],
            $config['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        // 升级程序
        // 2026-04-13
        // ===== 自动升级数据库结构 =====

        // 要检查的字段
        $needColumns = [
            'dwz_expire_time' => "ALTER TABLE huoma_dwz ADD COLUMN dwz_expire_time VARCHAR(32) DEFAULT NULL COMMENT '链接到期时间'",
            'dwz_expire_jump' => "ALTER TABLE huoma_dwz ADD COLUMN dwz_expire_jump TEXT COMMENT '到期跳转链接'"
        ];
        
        // 查询已有字段
        $stmt = $pdo->query("SHOW COLUMNS FROM huoma_dwz");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // 检查并补充字段
        foreach ($needColumns as $col => $sql) {
            if (!in_array($col, $columns)) {
                $pdo->exec($sql);
            }
        }
        
        // 每页展示
        $length = 12;

        // 总数
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM huoma_dwz WHERE dwz_creat_user = :user");
        $stmt->execute([':user' => $LoginUser]);
        $dwzNum = (int)$stmt->fetchColumn();

        $allpage = $dwzNum > 0 ? ceil($dwzNum / $length) : 1;
        $offset = ($page - 1) * $length;

        $prepage = $page > 1 ? $page - 1 : 1;
        $nextpage = $page < $allpage ? $page + 1 : $allpage;

        // 查询
        $stmt = $pdo->prepare("
            SELECT * FROM huoma_dwz
            WHERE dwz_creat_user = :user
            ORDER BY ID DESC
            LIMIT :offset, :length
        ");

        $stmt->bindValue(':user', $LoginUser, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();

        $list = $stmt->fetchAll();

        if($list){
            $result = [
                'dwzList' => $list,
                'dwzNum' => $dwzNum,
                'prepage' => $prepage,
                'nextpage' => $nextpage,
                'allpage' => (int)$allpage,
                'page' => $page,
                'code' => 200,
                'msg' => '获取成功'
            ];
        }else{
            $result = [
                'code' => 204,
                'msg' => '暂无短网址'
            ];
        }

    } catch (PDOException $e) {
        $result = [
            'code' => 500,
            'msg' => '数据库错误'
            // 生产环境不要暴露 $e->getMessage()
        ];
    }

}else{
    $result = [
        'code' => 201,
        'msg' => '未登录'
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>