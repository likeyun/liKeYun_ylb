<?php
// 页面编码
header("Content-type:application/json");

// 判断登录状态
session_start();
if(isset($_SESSION["yinliubao"])){

    // 已登录
    $page = isset($_GET['p']) ? intval($_GET['p']) : 1;
    if ($page <= 0) $page = 1;

    // 当前登录的用户
    $LoginUser = $_SESSION["yinliubao"];

    // 数据库配置
    include '../../../../Db.php';

    try {
        // 建立PDO连接
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 每页数量
        $length = 12;
        $offset = ($page - 1) * $length;

        // 总数
        $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM ylbPlugin_wxdmQk WHERE data_create_user = :user");
        $stmtTotal->execute([':user' => $LoginUser]);
        $totalNum = (int)$stmtTotal->fetchColumn();

        // 总页码
        $allpage = $totalNum > 0 ? ceil($totalNum / $length) : 1;

        // 上一页、下一页
        $prepage = ($page > 1) ? $page - 1 : 1;
        $nextpage = ($page < $allpage) ? $page + 1 : $allpage;

        // 查询当前页数据
        $stmt = $pdo->prepare("SELECT * FROM ylbPlugin_wxdmQk WHERE data_create_user = :user ORDER BY ID DESC LIMIT :offset, :length");
        $stmt->bindValue(':user', $LoginUser, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();
        $getDataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($getDataList){
            $result = [
                'getDataList' => $getDataList,
                'totalNum' => $totalNum,
                'prepage' => $prepage,
                'nextpage' => $nextpage,
                'allpage' => $allpage,
                'page' => $page,
                'code' => 200,
                'msg' => '获取成功'
            ];
        }else{
            $result = ['code' => 204, 'msg' => '暂无数据'];
        }

    } catch (PDOException $e) {
        $result = ['code' => 500, 'msg' => '数据库错误: '.$e->getMessage()];
    }

}else{
    // 未登录
    $result = ['code' => 201, 'msg' => '未登录'];
}

// 输出JSON
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
