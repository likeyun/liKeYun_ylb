<?php

// 字符编码
header("Content-type:application/json");
 
// 获取选择的文件
$selectedFile = $_FILES["file"]["name"];

// 接收参数
$qun_id = trim(intval($_POST['qun_id']));
 
// 允许上传的后缀
$allowedExts = array("gif", "jpeg", "jpg", "png");

// 获取后缀名
$tempFile = explode(".", $selectedFile);
$extension = end($tempFile);

// 获取到文件名（不含后缀名）
$wenJianMing = substr($selectedFile, 0, strrpos($selectedFile, "."));

// 判断文件类型
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
        
        // 上传失败
        $result = array(
            'code' => 201,
            'msg' => '上传失败'
        );
    }else{
        
        // 判断登录状态
        session_start();
        if(isset($_SESSION["yinliubao"])){
            
            // 当前登录的用户
            $loginUser = $_SESSION["yinliubao"];
            
            // 文件指纹
            // 以区分同一文件名时
            // 各文件的唯一
            $fingerPrint = rand(100,999);
            
            // 新的文件（文件名+指纹+后缀名）
            $newFile = $wenJianMing.'-'.$fingerPrint.'.'.$extension;
            
            // 上传文件
            move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/".$newFile);
            
            // 获取HTTP协议
            $protoCol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            
            // 图片文件链接
            $imgFileUrl = $protoCol.'://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
            
            // 图片储存目录
            $imgFileFolder = dirname(dirname($imgFileUrl));
            
            // 子码ID生成
            $zm_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 参数
            $uploadQunzmParams = [
                'qun_id' => $qun_id,
                'zm_id' => $zm_id,
                'zm_qrcode' => $imgFileFolder."/upload/".$newFile,
                'zm_update_time' => date('Y-m-d H:i:s')
            ];
            
            // 将上传的二维码添加至当前qunid的子码中
            $uploadQunzmResult = $db->set_table('huoma_qun_zima')->add($uploadQunzmParams);
            
            // 执行成功
            if($uploadQunzmResult){
                
                // 将素材添加至素材库
                // 素材ID
                $sucai_id = rand(100000,999999);
                
                // 参数
                $uploadSuCaiParams = [
                    'sucai_id'=>$sucai_id,
                    'sucai_filename'=>$newFile,
                    'sucai_beizhu'=>$wenJianMing,
                    'sucai_upload_user'=>$loginUser,
                    'sucai_type'=>1,
                    'sucai_size'=>$_FILES["file"]["size"]
                ];
        
                // 将素材记录插入数据库
                $uploadSuCai = $db->set_table('huoma_sucai')->add($uploadSuCaiParams);
                
                // 判断添加至素材库结果
                if($uploadSuCai){
                    
                    // 上传成功
                    $result = array(
                        'code' => 200,
                        'msg' => '上传成功'
                    );
                }else{
                    
                    // 添加至素材库失败
                    $result = array(
                        'code' => 203,
                        'msg' => '添加至素材库失败'
                    );
                }
            }else{
                
                // 上传失败
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
    
    // 此类文件不能上传
    $result = array(
        'code' => 202,
        'msg' => '此类文件不能上传'
    );
}

// 输出JSON
echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>
