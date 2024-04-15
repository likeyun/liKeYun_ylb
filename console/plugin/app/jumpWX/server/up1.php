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
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        $file1 = $_FILES["file1"];
        
        // 允许上传的后缀
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        
        // 处理文件1
        $file1Name = $file1["name"];
        $file1TmpName = $file1["tmp_name"];
        
        // 获取后缀名1
        $tempFile1 = explode(".", $file1Name);
        $extension1 = end($tempFile1);
        
        // 获取到文件名1（不含后缀名）
        $wenJianMing1 = substr($file1Name, 0, strrpos($file1Name, "."));
        
        // 文件1类型
        $file1Type = $_FILES["file1"]["type"];
        
        // 判断文件1后缀名
        if(!in_array($extension1, $allowedExts)) {
            
            // 不符合
            $result = array(
                "code" => 202,
                "msg" => "不支持的文件格式"
            );
            exit;
        }
        
        // 判断文件1类型
        if ($file1Type == "image/gif"
        || $file1Type == "image/jpeg"
        || $file1Type == "image/jpg"
        || $file1Type == "image/pjpeg"
        || $file1Type == "image/x-png"
        || $file1Type == "image/png") {
            
            // 符合
            // 判断文件1大小
            if($_FILES["file1"]["size"] < 10485760) {
                
                // 符合
                if($_FILES["file1"]["error"] > 0) {
                    
                    // 上传失败
                    $result = array(
                        "code" => 202,
                        "msg" => "上传失败！"
                    );
                }else {
                    
                    // 上传成功
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
                        $newFile = $wenJianMing1.'_'.$fingerPrint.'.'.$extension1;
                        
                        // 将1移动到upload目录
                        move_uploaded_file($file1TmpName, "../img/upload/" . $newFile);
                        
                        // 获取HTTP协议
                        $protoCol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        
                        // 图片文件链接
                        $imgFileUrl = $protoCol.'://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
                        
                        // 图片储存目录
                        $imgFileFolder = dirname(dirname($imgFileUrl));
            
                        // 数据库配置
                        include '../../../../Db.php';
                        
                        // 实例化类
                        $db = new DB_API($config);
                        
                        // 素材ID
                        $sucai_id = rand(100000,999999);
                        
                        // 需向数据库插入的参数
                        $uploadSuCaiParams = [
                            'sucai_id'=>$sucai_id,
                            'sucai_filename'=>$newFile,
                            'sucai_beizhu'=>$wenJianMing1,
                            'sucai_upload_user'=>$loginUser,
                            'sucai_type'=>1,
                            'sucai_size'=>$_FILES["file1"]["size"]
                        ];
                
                        // 执行SQL
                        $uploadSuCai = $db->set_table('huoma_sucai')->add($uploadSuCaiParams);
                        
                        // 执行结果
                        if($uploadSuCai){
                            
                            // 执行成功
                            $result = array(
                                "code" => 200,
                                "msg" => "图标上传成功",
                                "url" => $imgFileFolder . "/img/upload/" . $wenJianMing1 . '_' . $fingerPrint . '.' . $extension1
                            );
                        }else{
                            
                            // 执行失败
                            $result = array(
                                "code" => 202,
                                "msg" => "上传失败！无法添加到素材库~"
                            );
                            
                            // 删除文件
                            unlink('../../../../upload/'.$newFile);
                        }
                    }else {
                        
                        // 未登录
                        $result = array(
                            "code" => 202,
                            "msg" => "未登录"
                        );
                    } 
                }
            }else {
                
                // 不符合
                $result = array(
                    "code" => 202,
                    "msg" => "文件大小不得>10MB"
                );
            }
        }else {
            
            // 不符合
            $result = array(
                "code" => 202,
                "msg" => "不支持的文件类型"
            );
        }
        
        // 输出JSON
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
    }

?>
