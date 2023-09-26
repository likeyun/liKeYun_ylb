<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     * 程序用途：上传图片到素材库
     * 最后维护日期：2023-06-03
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     */

    // 字符编码
    header("Content-type:application/json");
     
    // 获取选择的文件
    $selectedFile = $_FILES["file"]["name"];
     
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
                // 以区分多次上传同一文件时
                // 上传后均为独立的文件
                $fingerPrint = rand(1000,9999);
                
                // 新的文件（文件名+_文件指纹+后缀名）
                $newFile = $wenJianMing.'_'.$fingerPrint.'.'.$extension;
                
                // 上传文件
                move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/".$newFile);
                
                // 获取HTTP协议
                $protoCol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                
                // 图片文件链接
                $imgFileUrl = $protoCol.'://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
                
                // 图片储存目录
                $imgFileFolder = dirname(dirname($imgFileUrl));
    
                // 数据库配置
                include '../Db.php';
                
                // 实例化类
                $db = new DB_API($config);
                
                // 素材ID
                $sucai_id = rand(100000,999999);
                
                // 需向数据库插入的参数
                $uploadSuCaiParams = [
                    'sucai_id'=>$sucai_id,
                    'sucai_filename'=>$newFile,
                    'sucai_beizhu'=>$wenJianMing,
                    'sucai_upload_user'=>$loginUser,
                    'sucai_type'=>1,
                    'sucai_size'=>$_FILES["file"]["size"]
                ];
        
                // 执行SQL
                $uploadSuCai = $db->set_table('huoma_sucai')->add($uploadSuCaiParams);
                
                // 执行结果
                if($uploadSuCai){
                    
                    // 执行成功
                    $result = array(
                        "code" => 200,
                        "msg" => "上传成功",
                        "url" => $imgFileFolder . "/upload/" . $wenJianMing . '_' . $fingerPrint . '.' . $extension
                    );
                }else{
                    
                    // 执行失败
                    $result = array(
                        "code" => 202,
                        "msg" => "上传失败！无法添加到素材库~"
                    );
                    
                    // 删除文件
                    unlink('../upload/'.$newFile);
                }
                
            }else{
                
                // 上传失败
                $result = array(
                    'code' => 201,
                    'msg' => '未登录'
                );
            }
        }
    }else{
        
        if($_FILES["file"]["size"] > 10485760){
            
            // 上传失败（文件大小超出10MB）
            $result = array(
                'code' => 202,
                'msg' => '文件大小超出10MB，无法上传！'
            );
        }else{
            
            // 上传失败（其它情况）
            $result = array(
                'code' => 202,
                'msg' => '文件类型不符合规则！只能上传jpg、jpeg、png、gif'
            );
        }
        
    }
    
    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>
