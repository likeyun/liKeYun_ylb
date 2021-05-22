<?php
// 返回json格式的数据
header("Content-type:application/json");

// 开启session，判断登陆状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 获取原始文件名
	$filename = $_FILES["file"]["name"];
	 
	// 获取文件后缀名
	$hzm = substr($filename,strpos($filename,"."));
	 
	// 设置新文件名
	$newfilename = date("Y-m-d")."-".rand(100,999);

	// 允许上传的文件后缀
	$allowedExts = array("txt");
	$temp = explode(".", $filename);
	$extension = end($temp);
	if ((($_FILES["file"]["type"] == "text/plain"))
	&& ($_FILES["file"]["size"] < 2048000)   // 小于 2000 kb
	&& in_array($extension, $allowedExts))
	{
	    if ($_FILES["file"]["error"] > 0)
	    {
	        $upload_result = array(
	            "res" => 404,
	            "msg" => "上传时发生错误"
	        );
	    }
	    else
	    {
	    // 此处可以输出文件的详细信息
	    if (file_exists("upload/" . $newfilename.$hzm))
	        {
	            //
	        }
	        else
	        {
	        	// 上传到upload目录
	            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $newfilename.$hzm);
	            // 上传成功后，就可以执行插入数据库操作
	            // 数据库配置
				include '../db_config/db_config.php';

				// 创建连接
				$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

				// 插入数据库的字符编码设为utf8
				mysqli_query($conn, "SET NAMES UTF-8");

				// 读取上传成功的txt
	            $txt_str = file_get_contents ('./upload/'.$newfilename.$hzm);
	            // 分行存入数组
				$txt_arr = explode("\n",$txt_str );
				// 遍历每行
				foreach ($txt_arr as $txt_row){
					// 将取得的字符串进行分割
					// 截取|后面的内容
					$tianshu = substr($txt_row, strripos($txt_row, "|") + 1);
					// 截取|前面的内容
					$yaoqingma = substr($txt_row, 0, strrpos($txt_row, "|"));
					// 插入数据库
				    $sql_insert_yqm = "INSERT INTO huoma_yqm (yqm,yqm_status,yqm_daynum) VALUES ('$yaoqingma','1','$tianshu')";
				    if ($conn->query($sql_insert_yqm) === TRUE) {
					    // 导入成功
					    $result = array(
					        "code" => 200,
					        "msg" => "导入完成"
					    );
					} else {
					    // 导入失败
					    $result = array(
					        "code" => 200,
					        "msg" => "导入完成"
					    );
					}
				}

				// 断开数据库连接
				$conn->close();
	        }
	    }
	}
	else
	{
	    $result = array(
	        "code" => 403,
	        "msg" => "只能上传TXT格式的文件"
	    );
	}
}else{
	$result = array(
        "code" => 402,
        "msg" => "未登录"
    );
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>