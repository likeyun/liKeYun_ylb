<?php
// 设置页面返回的字符编码为json格式
header("Content-type:application/json");

// 开启session，验证登录状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$title = $_POST["title"];
	$keywords = $_POST["keywords"];
	$description = $_POST["description"];
	$favicon = $_POST["favicon"];

	// 设置字符编码为utf-8
	mysqli_query($conn, "SET NAMES UTF-8");
	// 更新数据库
	mysqli_query($conn,"UPDATE huoma_set SET title='$title',keywords='$keywords',description='$description',favicon='$favicon' WHERE id='1'");
	$result = array(
		"code" => "100",
		"msg" => "设置成功"
	);
	
}else{
	$result = array(
		"code" => "105",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>