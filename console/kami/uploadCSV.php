<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     * 程序用途：上传excel
     * 最后维护日期：2023-12-21
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     */
     
    // 字符编码
    header("Content-type:application/json");
     
    // 获取选择的文件
    $selectedFile = $_FILES["file"]["name"];
     
    // 允许上传的后缀
    $allowedExts = array("csv");
    
    // 获取后缀名
    $tempFile = explode(".", $selectedFile);
    $extension = end($tempFile);
    
    // 获取到文件名（不含后缀名）
    $wenJianMing = substr($selectedFile, 0, strrpos($selectedFile, "."));
    
    // 判断文件类型
    if($_FILES["file"]["type"] == "text/csv") {
        
        // 判断文件大小
        if($_FILES["file"]["size"] > 2097152) {
            
            // 上传失败
            $result = array(
                'code' => 202,
                'msg' => '文件大小不能超过2MB'
            );
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 判断文件后缀名
        if(!in_array($extension, $allowedExts)) {
            
            // 上传失败
            $result = array(
                'code' => 202,
                'msg' => '此类文件不能上传（后缀名不符合）'
            );
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 判断登录状态
        session_start();
        if(isset($_SESSION["yinliubao"])){
            
            // 当前登录的用户
            $loginUser = $_SESSION["yinliubao"];
            
            // 文件指纹
            // 以区分多次上传同一文件时
            // 上传后均为独立的文件
            $fingerPrint = date('YmdHis');
            
            // 新的文件（文件名+_文件指纹+后缀名）
            $newFile = $wenJianMing.'_'.$fingerPrint.'.'.$extension;
            
            // 上传文件
            move_uploaded_file($_FILES["file"]["tmp_name"], "kmFiles/".$newFile);
            
            // 上传成功
            $result = array(
                "code" => 200,
                "msg" => "已上传",
                "filePath" => "./kmFiles/".$newFile
            );
        }else {
            
            // 未登录
            $result = array(
                'code' => 202,
                'msg' => '未登录'
            );
        }
    }else {
        
        // 上传失败
        $result = array(
            'code' => 202,
            'msg' => '此类文件不能上传（文件类型不符合）'
        );
    }
    
    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>
