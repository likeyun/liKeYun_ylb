<?php
header("Content-type:application/json;charset=utf-8");

session_start();
if(isset($_SESSION["yinliubao"])){

    $dwz_rkym = trim($_POST['dwz_rkym'] ?? '');
    $dwz_zzym = trim($_POST['dwz_zzym'] ?? '');
    $dwz_dlym = trim($_POST['dwz_dlym'] ?? '');
    $dwz_dlws = intval($_POST['dwz_dlws'] ?? 0);
    $dwz_type = trim($_POST['dwz_type'] ?? '');
    $dwz_urls = trim($_POST['dwz_urls'] ?? '');

    $loginUser = $_SESSION["yinliubao"];

    function creatKey($length){
        $str = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
        return substr(str_shuffle($str),0,$length);
    }

    include '../Db.php';

    if(!$dwz_rkym){
        $result = ['code'=>203,'msg'=>'入口域名不可为空'];
    }elseif(!$dwz_zzym){
        $result = ['code'=>203,'msg'=>'中转域名不可为空'];
    }elseif(!$dwz_dlym){
        $result = ['code'=>203,'msg'=>'短链域名不可为空'];
    }elseif(!$dwz_dlws){
        $result = ['code'=>203,'msg'=>'短链位数不可为空'];
    }elseif(!$dwz_type){
        $result = ['code'=>203,'msg'=>'访问限制不可为空'];
    }elseif(!$dwz_urls){
        $result = ['code'=>203,'msg'=>'目标链接不可为空'];
    }else{

        try{
            $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn,$config['db_user'],$config['db_pass'],[
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // 提取URL
            preg_match_all('/https?:\/\/[^\s]+/i', $dwz_urls, $matches);

            if(empty($matches[0])){
                echo json_encode(['code'=>202,'msg'=>'未匹配到链接'],JSON_UNESCAPED_UNICODE);
                exit;
            }

            $urls = $matches[0];
            $createResult = [];
            $createNum = 0;

            // 开启事务（关键优化）
            $pdo->beginTransaction();

            $stmtInsert = $pdo->prepare("
                INSERT INTO huoma_dwz 
                (dwz_title,dwz_rkym,dwz_zzym,dwz_dlym,dwz_type,dwz_url,dwz_creat_user,dwz_key,dwz_today_pv,dwz_id)
                VALUES
                (:title,:rkym,:zzym,:dlym,:type,:url,:user,:key,:pv,:id)
            ");

            $stmtCheck = $pdo->prepare("SELECT 1 FROM huoma_dwz WHERE dwz_key=? LIMIT 1");

            foreach($urls as $url){

                // 生成唯一key（最多5次）
                $dwzKey = '';
                for($i=0;$i<5;$i++){
                    $tmp = creatKey($dwz_dlws);
                    $stmtCheck->execute([$tmp]);
                    if(!$stmtCheck->fetch()){
                        $dwzKey = $tmp;
                        break;
                    }
                }

                if(!$dwzKey){
                    continue;
                }

                $dwz_id = rand(100000,999999);
                $dwz_title = '批量生成-'.$dwzKey;

                $res = $stmtInsert->execute([
                    ':title'=>$dwz_title,
                    ':rkym'=>$dwz_rkym,
                    ':zzym'=>$dwz_zzym,
                    ':dlym'=>$dwz_dlym,
                    ':type'=>$dwz_type,
                    ':url'=>$url,
                    ':user'=>$loginUser,
                    ':key'=>$dwzKey,
                    ':pv'=>json_encode(['pv'=>0,'date'=>date('Y-m-d')]),
                    ':id'=>$dwz_id
                ]);

                if($res){
                    $createNum++;
                    $createResult[] = $dwz_dlym.'/'.$dwzKey;
                }
            }

            // 提交
            $pdo->commit();

            $result = [
                'code'=>200,
                'msg'=>"创建完成，共成功{$createNum}条",
                'createNum'=>$createNum,
                'dwzList'=>$createResult
            ];

        }catch(PDOException $e){
            if($pdo->inTransaction()){
                $pdo->rollBack();
            }
            $result = ['code'=>500,'msg'=>'数据库错误'];
        }
    }

}else{
    $result = ['code'=>201,'msg'=>'未登录'];
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>