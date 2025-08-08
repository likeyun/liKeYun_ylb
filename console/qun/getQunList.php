<?php

// 页面编码
header("Content-type:application/json");

// 开启 Session
session_start();

if (isset($_SESSION["yinliubao"])) {

    // 当前登录用户
    $LoginUser = $_SESSION["yinliubao"];

    // 当前页码
    $page = isset($_GET['p']) ? intval($_GET['p']) : 1;
    $length = 10;
    $offset = ($page - 1) * $length;

    // 引入数据库配置
    include '../Db.php';

    try {
        // 创建 PDO 实例
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 检查 longpress_num 字段是否存在于 huoma_qun_zima 表
        $checkStmt = $pdo->query("SHOW COLUMNS FROM huoma_qun_zima LIKE 'longpress_num'");
        if ($checkStmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE huoma_qun_zima ADD longpress_num INT(10) DEFAULT 0 COMMENT '长按次数'");
        }

        // 获取群活码总数
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM huoma_qun WHERE qun_creat_user = :user");
        $stmt->execute([':user' => $LoginUser]);
        $qunNum = $stmt->fetchColumn();

        // 总页数
        $allpage = ceil($qunNum / $length);
        $prepage = ($page > 1) ? $page - 1 : 1;
        $nextpage = ($page < $allpage) ? $page + 1 : $allpage;

        // 查询群活码列表（分页）
        $stmt = $pdo->prepare("
            SELECT * FROM huoma_qun
            WHERE qun_creat_user = :user
            ORDER BY ID DESC
            LIMIT :offset, :length
        ");
        $stmt->bindValue(':user', $LoginUser, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();
        $qunList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($qunList) {
            $result = [
                'qunList' => $qunList,
                'qunNum' => $qunNum,
                'prepage' => $prepage,
                'nextpage' => $nextpage,
                'allpage' => $allpage,
                'page' => $page,
                'code' => 200,
                'msg' => 'SUCCESS'
            ];
        } else {
            $result = [
                'code' => 204,
                'msg' => '暂无群活码'
            ];
        }

    } catch (PDOException $e) {
        $result = [
            'code' => 500,
            'msg' => '数据库连接失败: ' . $e->getMessage()
        ];
    }

} else {
    // 未登录
    $result = [
        'code' => 201,
        'msg' => '未登录'
    ];
}

// 输出 JSON
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>