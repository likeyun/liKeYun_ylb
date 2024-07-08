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
        
        $file2 = $_FILES["file2"];
        
        // 允许上传的后缀
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        
        // 处理文件2
        $file2Name = $file2["name"];
        $file2TmpName = $file2["tmp_name"];
        
        // 获取后缀名2
        $tempFile2 = explode(".", $file2Name);
        $extension2 = end($tempFile2);
        
        // 获取到文件名2（不含后缀名）
        $wenJianMing2 = substr($file2Name, 0, strrpos($file2Name, "."));
        
        // 文件2类型
        $file2Type = $_FILES["file2"]["type"];
        
        // 判断文件1后缀名
        if(!in_array($extension2, $allowedExts)) {
            
            // 不符合
            $result = array(
                "code" => 202,
                "msg" => "不支持的文件格式"
            );
            exit;
        }
        
        // 判断文件1类型
        if ($file2Type == "image/gif"
        || $file2Type == "image/jpeg"
        || $file2Type == "image/jpg"
        || $file2Type == "image/pjpeg"
        || $file2Type == "image/x-png"
        || $file2Type == "image/png") {
            
            // 符合
            // 判断文件1大小
            if($_FILES["file2"]["size"] < 10485760) {
                
                // 符合
                if($_FILES["file2"]["error"] > 0) {
                    
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
                        $newFile = $wenJianMing2.'_'.$fingerPrint.'.'.$extension2;
                        
                        // 将1移动到upload目录
                        move_uploaded_file($file2TmpName, "../../../../upload/" . $newFile);
                        
                        // 获取HTTP协议
                        $protoCol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        
                        // 自定义图片域名
                        // 例如你的域名是：www.qq.com
                        // 直接填进去
                        // $imgDomain = 'www.qq.com';
                        
                        // 自动获取后台地址作为图片域名
                        $imgDomain = $_SERVER['SERVER_NAME'];
                        
                        // 图片文件链接
                        $imgFileUrl = $protoCol.'://'.$imgDomain.$_SERVER["REQUEST_URI"];
                        
                        // 图片储存目录
                        $imgFileFolder = dirname(dirname(dirname(dirname(dirname($imgFileUrl)))));
            
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
                            'sucai_beizhu'=>$wenJianMing2,
                            'sucai_upload_user'=>$loginUser,
                            'sucai_type'=>1,
                            'sucai_size'=>$_FILES["file2"]["size"]
                        ];
                
                        // 执行SQL
                        $uploadSuCai = $db->set_table('huoma_sucai')->add($uploadSuCaiParams);
                        
                        // 执行结果
                        if($uploadSuCai){
                            
                            // 执行成功
                            $result = array(
                                "code" => 200,
                                "msg" => "图片上传成功",
                                "url" => $imgFileFolder . "/upload/" . $wenJianMing2 . '_' . $fingerPrint . '.' . $extension2
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
