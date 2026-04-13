<?php
header("Content-type:application/json;charset=utf-8");

session_start();
if(isset($_SESSION["yinliubao"])){

    // 参数
    $dwz_title = trim($_POST['dwz_title'] ?? '');
    $dwz_rkym = trim($_POST['dwz_rkym'] ?? '');
    $dwz_zzym = trim($_POST['dwz_zzym'] ?? '');
    $dwz_dlym = trim($_POST['dwz_dlym'] ?? '');
    $dwz_lxym = trim($_POST['dwz_lxym'] ?? '');
    $dwz_dlws = intval($_POST['dwz_dlws'] ?? 0);
    $dwz_type = trim($_POST['dwz_type'] ?? '');
    $dwz_url = trim($_POST['dwz_url'] ?? '');
    $dwz_lxymStatus = intval($_POST['dwz_lxymStatus'] ?? 0);
    $dwz_creat_user = $_SESSION["yinliubao"];

    // URL校验（更严谨）
    function is_url($url){
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    // 参数校验
    if(!$dwz_title){
        $result = ['code'=>203,'msg'=>'标题未设置'];
    }elseif(!$dwz_rkym){
        $result = ['code'=>203,'msg'=>'入口域名未选择'];
    }elseif(!$dwz_zzym){
        $result = ['code'=>203,'msg'=>'中转域名未选择'];
    }elseif(!$dwz_dlym){
        $result = ['code'=>203,'msg'=>'短链域名未选择'];
    }elseif(!$dwz_dlws){
        $result = ['code'=>203,'msg'=>'短链位数未选择'];
    }elseif(!$dwz_type){
        $result = ['code'=>203,'msg'=>'访问限制未选择'];
    }elseif(!$dwz_url){
        $result = ['code'=>203,'msg'=>'目标链接未填写'];
    }elseif(!is_url($dwz_url)){
        $result = ['code'=>203,'msg'=>'目标链接格式错误'];
    }else{

        include '../Db.php';

        try{
            // PDO
            $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn,$config['db_user'],$config['db_pass'],[
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // 检查轮询域名
            if($dwz_lxymStatus == 1){
                $stmt = $pdo->prepare("SELECT 1 FROM huoma_domain WHERE domain_type=6 LIMIT 1");
                $stmt->execute();
                if(!$stmt->fetch()){
                    echo json_encode(['code'=>202,'msg'=>'域名库没有轮询域名'],JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            // 生成ID
            $dwz_id = '10'.mt_rand(100000,999999);

            // key生成
            function creatKey($length){
                $str = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
                return substr(str_shuffle($str),0,$length);
            }

            // 生成唯一key（最多尝试5次）
            $dwzKey = '';
            for($i=0;$i<5;$i++){
                $tmpKey = creatKey($dwz_dlws);
                $stmt = $pdo->prepare("SELECT 1 FROM huoma_dwz WHERE dwz_key=? LIMIT 1");
                $stmt->execute([$tmpKey]);
                if(!$stmt->fetch()){
                    $dwzKey = $tmpKey;
                    break;
                }
            }

            if(!$dwzKey){
                echo json_encode(['code'=>500,'msg'=>'生成短链失败'],JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 插入
            $sql = "INSERT INTO huoma_dwz 
            (dwz_title,dwz_today_pv,dwz_rkym,dwz_zzym,dwz_dlym,dwz_type,dwz_url,dwz_lxymStatus,dwz_creat_user,dwz_key,dwz_id)
            VALUES
            (:title,:pv,:rkym,:zzym,:dlym,:type,:url,:lxymStatus,:user,:key,:id)";

            $stmt = $pdo->prepare($sql);
            $res = $stmt->execute([
                ':title'=>$dwz_title,
                ':pv'=>json_encode(['pv'=>0,'date'=>date('Y-m-d')],JSON_UNESCAPED_UNICODE),
                ':rkym'=>$dwz_rkym,
                ':zzym'=>$dwz_zzym,
                ':dlym'=>$dwz_dlym,
                ':type'=>$dwz_type,
                ':url'=>$dwz_url,
                ':lxymStatus'=>$dwz_lxymStatus,
                ':user'=>$dwz_creat_user,
                ':key'=>$dwzKey,
                ':id'=>$dwz_id
            ]);

            if($res){
                $result = ['code'=>200,'msg'=>'创建成功','key'=>$dwzKey];
            }else{
                $result = ['code'=>202,'msg'=>'创建失败'];
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