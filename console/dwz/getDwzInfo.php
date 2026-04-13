<?php

header("Content-type:application/json;charset=utf-8");

session_start();
if(isset($_SESSION["yinliubao"])){

    $dwz_id = trim($_GET['dwz_id'] ?? '');

    if(!$dwz_id){
        $result = ['code'=>203,'msg'=>'非法请求'];
    }else{

        include '../Db.php';

        try{
            $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn,$config['db_user'],$config['db_pass'],[
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // 查询（建议加 LIMIT 1）
            $stmt = $pdo->prepare("SELECT * FROM huoma_dwz WHERE dwz_id = :id LIMIT 1");
            $stmt->execute([':id'=>$dwz_id]);

            $data = $stmt->fetch();

            if($data){
                $result = [
                    'code'=>200,
                    'msg'=>'获取成功',
                    'dwzInfo'=>$data
                ];
            }else{
                $result = [
                    'code'=>204,
                    'msg'=>'无结果'
                ];
            }

        }catch(PDOException $e){
            $result = [
                'code'=>500,
                'msg'=>'数据库错误'
            ];
        }
    }

}else{
    $result = [
        'code'=>201,
        'msg'=>'未登录或登录过期'
    ];
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>