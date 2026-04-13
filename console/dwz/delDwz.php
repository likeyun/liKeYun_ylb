<?php
header("Content-type:application/json;charset=utf-8");

session_start();
if(isset($_SESSION["yinliubao"])){

    $dwz_id = trim($_GET['dwz_id'] ?? '');

    if(!$dwz_id){
        $result = ['code'=>203,'msg'=>'非法请求'];
    }else{

        $LoginUser = $_SESSION["yinliubao"];
        include '../Db.php';

        try{
            // PDO
            $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn,$config['db_user'],$config['db_pass'],[
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // ✅ 直接带条件删除（更安全，不需要先查）
            $stmt = $pdo->prepare("DELETE FROM huoma_dwz WHERE dwz_id = :id AND dwz_creat_user = :user");
            $stmt->execute([
                ':id' => $dwz_id,
                ':user' => $LoginUser
            ]);

            // 判断是否真的删除成功
            if($stmt->rowCount() > 0){
                $result = ['code'=>200,'msg'=>'删除成功'];
            }else{
                // 可能是不存在 或 无权限
                $result = ['code'=>202,'msg'=>'删除失败'];
            }

        }catch(PDOException $e){
            $result = [
                'code'=>500,
                'msg'=>'数据库错误'
                // 生产环境不要返回 $e->getMessage()
            ];
        }
    }

}else{
    $result = ['code'=>201,'msg'=>'未登录'];
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>