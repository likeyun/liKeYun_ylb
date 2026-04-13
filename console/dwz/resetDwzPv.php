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

            // 今日PV结构
            $todayPv = json_encode([
                'pv' => 0,
                'date' => date('Y-m-d')
            ], JSON_UNESCAPED_UNICODE);

            // ✅ 原子更新（直接带用户条件）
            $stmt = $pdo->prepare("
                UPDATE huoma_dwz 
                SET dwz_pv = 0,
                    dwz_today_pv = :today_pv
                WHERE dwz_id = :id AND dwz_creat_user = :user
            ");

            $stmt->execute([
                ':today_pv' => $todayPv,
                ':id' => $dwz_id,
                ':user' => $LoginUser
            ]);

            if($stmt->rowCount() > 0){
                $result = ['code'=>200,'msg'=>'已重置'];
            }else{
                // 无权限 或 不存在
                $result = ['code'=>202,'msg'=>'重置失败或无权限'];
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