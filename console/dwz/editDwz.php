<?php
header("Content-type:application/json;charset=utf-8");

session_start();
if(isset($_SESSION["yinliubao"])){

    $dwz_title = trim($_POST['dwz_title'] ?? '');
    $dwz_rkym = trim($_POST['dwz_rkym'] ?? '');
    $dwz_zzym = trim($_POST['dwz_zzym'] ?? '');
    $dwz_dlym = trim($_POST['dwz_dlym'] ?? '');
    $dwz_key = trim($_POST['dwz_key'] ?? '');
    $dwz_type = trim($_POST['dwz_type'] ?? '');
    $dwz_url = trim($_POST['dwz_url'] ?? '');
    $dwz_lxymStatus = intval($_POST['dwz_lxymStatus'] ?? 0);
    $dwz_id = trim($_POST['dwz_id'] ?? '');

    $dwz_expire_time = trim($_POST['dwz_expire_time'] ?? '');
    $dwz_expire_jump = trim($_POST['dwz_expire_jump'] ?? '');

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
    }elseif(!$dwz_type){
        $result = ['code'=>203,'msg'=>'访问限制未选择'];
    }elseif(!$dwz_key){
        $result = ['code'=>203,'msg'=>'短网址Key未填写'];
    }elseif($dwz_lxymStatus === null){
        $result = ['code'=>203,'msg'=>'轮询状态未选择'];
    }elseif(!$dwz_url){
        $result = ['code'=>203,'msg'=>'目标链接未填写'];
    }elseif(!is_url($dwz_url)){
        $result = ['code'=>203,'msg'=>'目标链接格式错误'];
    }elseif($dwz_expire_jump && !is_url($dwz_expire_jump)){
        $result = ['code'=>203,'msg'=>'到期跳转URL错误'];
    }elseif(!$dwz_id){
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

            // 检查轮询域名
            if($dwz_lxymStatus == 1){
                $stmt = $pdo->prepare("SELECT 1 FROM huoma_domain WHERE domain_type=6 LIMIT 1");
                $stmt->execute();
                if(!$stmt->fetch()){
                    echo json_encode(['code'=>202,'msg'=>'域名库没有轮询域名'],JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            // 查询当前数据（校验归属）
            $stmt = $pdo->prepare("SELECT dwz_creat_user FROM huoma_dwz WHERE dwz_id=? LIMIT 1");
            $stmt->execute([$dwz_id]);
            $row = $stmt->fetch();

            if(!$row || $row['dwz_creat_user'] != $LoginUser){
                echo json_encode(['code'=>202,'msg'=>'非法请求'],JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 检查key是否被别人占用
            $stmt = $pdo->prepare("SELECT dwz_creat_user FROM huoma_dwz WHERE dwz_key=? LIMIT 1");
            $stmt->execute([$dwz_key]);
            $exist = $stmt->fetch();

            if($exist && $exist['dwz_creat_user'] != $LoginUser){
                echo json_encode(['code'=>203,'msg'=>'短网址Key已被占用'],JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 时间处理
            $expire_time = null;
            if($dwz_expire_time){
                $expire_time = str_replace('T',' ',$dwz_expire_time) . ':00';
            }

            // 更新
            $sql = "UPDATE huoma_dwz SET
                dwz_title = :title,
                dwz_rkym = :rkym,
                dwz_zzym = :zzym,
                dwz_dlym = :dlym,
                dwz_key = :key,
                dwz_type = :type,
                dwz_lxymStatus = :lxymStatus,
                dwz_url = :url,
                dwz_expire_time = :expire_time,
                dwz_expire_jump = :expire_jump
                WHERE dwz_id = :id AND dwz_creat_user = :user
            ";

            $stmt = $pdo->prepare($sql);
            $res = $stmt->execute([
                ':title'=>$dwz_title,
                ':rkym'=>$dwz_rkym,
                ':zzym'=>$dwz_zzym,
                ':dlym'=>$dwz_dlym,
                ':key'=>$dwz_key,
                ':type'=>$dwz_type,
                ':lxymStatus'=>$dwz_lxymStatus,
                ':url'=>$dwz_url,
                ':expire_time'=>$expire_time,
                ':expire_jump'=>$dwz_expire_jump,
                ':id'=>$dwz_id,
                ':user'=>$LoginUser
            ]);

            if($res){
                $result = ['code'=>200,'msg'=>'更新成功'];
            }else{
                $result = ['code'=>202,'msg'=>'更新失败'];
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