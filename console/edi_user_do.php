<?php
// 设置页面返回的字符编码为json格式
header("Content-type:application/json");

// 开启session，验证登录状态
session_start();
if(isset($_SESSION["huoma.admin"])){
	$lguser = $_SESSION["huoma.admin"];
	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$new_email = trim($_POST["new_email"]);
	$new_pwd = trim($_POST["new_pwd"]);
	$old_pwd = trim($_POST["old_pwd"]);
	$user_id = trim($_POST["user_id"]);

	if(empty($new_email)){
		$result = array(
			"code" => "101",
			"msg" => "邮箱不得为空"
		);
	}else if(empty($new_pwd)){
		$result = array(
			"code" => "102",
			"msg" => "密码不得为空"
		);
	}else if(empty($user_id)){
		$result = array(
			"code" => "103",
			"msg" => "非法请求"
		);
	}else if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)){
		$result = array(
			"code" => "104",
			"msg" => "邮箱无效"
		);
	}else if(strlen($new_pwd) < 8){
		$result = array(
			"code" => "105",
			"msg" => "密码长度不得小于8个字符"
		);
	}else if (preg_match_all("/([\x{4e00}-\x{9fa5}]+)/u", $new_pwd, $match)) {
		$result = array(
			"code" => "106",
			"msg" => "密码不得包含中文"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_user SET email='$new_email',pwd='$new_pwd' WHERE user_id=".$user_id);
		$result = array(
			"code" => "100",
			"msg" => "更新成功"
		);
		if ($old_pwd !== $new_pwd) {
			unset($_SESSION['huoma.admin']);
		}
	}
}else{
	$result = array(
		"code" => "105",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>