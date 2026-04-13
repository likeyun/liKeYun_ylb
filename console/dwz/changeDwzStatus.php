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
            $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn,$config['db_user'],$config['db_pass'],[
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // ✅ 原子切换（1 ↔ 2）
            $stmt = $pdo->prepare("
                UPDATE huoma_dwz
                SET dwz_status = IF(dwz_status = 1, 2, 1)
                WHERE dwz_id = :id AND dwz_creat_user = :user
            ");

            $stmt->execute([
                ':id' => $dwz_id,
                ':user' => $LoginUser
            ]);

            if($stmt->rowCount() > 0){

                // 再查当前状态（用于返回文案）
                $stmt2 = $pdo->prepare("SELECT dwz_status FROM huoma_dwz WHERE dwz_id = :id LIMIT 1");
                $stmt2->execute([':id'=>$dwz_id]);
                $row = $stmt2->fetch();

                $statusText = ($row && $row['dwz_status'] == 1) ? '已启用' : '已停用';

                $result = [
                    'code'=>200,
                    'msg'=>$statusText,
                    'status'=>$row['dwz_status'] ?? null
                ];

            }else{
                $result = ['code'=>202,'msg'=>'更新失败或无权限'];
            }

        }catch(PDOException $e){
            $result = ['code'=>500,'msg'=>'数据库错误'];
        }
    }

}else{
    $result = ['code'=>201,'msg'=>'未登录'];
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>