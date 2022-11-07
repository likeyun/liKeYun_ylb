<?php
// 编码
header("Content-type:application/json");
 
// 获取文件
$file = $_FILES["file"]["name"];

// 接收参数
$qun_id = trim($_POST['qun_id']);
 
// 获取文件后缀名
$hzm = substr($file,strpos($file,"."));
 
// 设置新文件名
$newfile = date("Y-m-d")."-".rand(100,999);
 
// 允许上传的后缀
$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $file);
$extension = end($temp);

if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/jpg")
|| ($_FILES["file"]["type"] == "image/pjpeg")
|| ($_FILES["file"]["type"] == "image/x-png")
|| ($_FILES["file"]["type"] == "image/png"))
&& ($_FILES["file"]["size"] < 10485760)
&& in_array($extension, $allowedExts)){
    
    // 判断上传结果
    if ($_FILES["file"]["error"] > 0){
        
        $result = array(
            'code' => 201,
            'msg' => '上传失败'
        );
    }else{
        
        // 上传文件
        move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/".$newfile.$hzm);
        $file_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
        $file_folder = dirname(dirname($file_url));
        
        // 上传成功
        session_start();
        if(isset($_SESSION["yinliubao"])){
            
            // 已登录
            // 子码ID生成
            $zm_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 数据库huoma_qun_zima表
        	$huoma_qun_zima = $db->set_table('huoma_qun_zima');
        
        	// 需要插入的数据
            $uploadQunzm_Sql = [
                'qun_id' => $qun_id,
                'zm_id' => $zm_id,
                'zm_qrcode' => $file_folder."/upload/".$newfile.$hzm,
                'zm_update_time' => date('Y-m-d H:i:s')
            ];
            $Result_uploadQunzm_Sql = $huoma_qun_zima->add($uploadQunzm_Sql);
            if($Result_uploadQunzm_Sql){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'msg' => '上传成功'
                );
            }else{
                
                // 失败
                $result = array(
                    'code' => 202,
                    'msg' => '上传失败'
                );
            }
            
        }else{
            
            // 未登录
            $result = array(
                'code' => 201,
                'msg' => '未登录'
            );
        }
    }
}else{
    
    $result = array(
        'code' => 202,
        'msg' => '此类文件不能上传'
    );
}

// 输出JSON
echo json_encode($result,true);

?>
