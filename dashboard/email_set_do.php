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
	$email_smtpserver = $_POST["email_smtpserver"];
	$email_smtpserverport = $_POST["email_smtpserverport"];
	$email_smtpusermail = $_POST["email_smtpusermail"];
	$email_smtpuser = $_POST["email_smtpuser"];
	$email_smtppass = $_POST["email_smtppass"];

	// 设置字符编码为utf-8
	mysqli_query($conn, "SET NAMES UTF-8");
	// 更新数据库
	mysqli_query($conn,"UPDATE huoma_set SET email_smtpserver='$email_smtpserver',email_smtpserverport='$email_smtpserverport',email_smtpusermail='$email_smtpusermail',email_smtpuser='$email_smtpuser',email_smtppass='$email_smtppass' WHERE id='1'");
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