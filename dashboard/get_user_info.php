<?php
header("Content-type:application/json");
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获取数据
	$userid = $_GET["userid"];

	if(empty($userid)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 获取用户信息
		$sql_userinfo = "SELECT * FROM huoma_user WHERE user_id = '$userid'";
		$result_userinfo = $conn->query($sql_userinfo);
		if ($result_userinfo->num_rows > 0) {
			while($row_userinfo = $result_userinfo->fetch_assoc()) {
				$user = $row_userinfo["user"];
				$pwd = $row_userinfo["pwd"];
				$email = $row_userinfo["email"];

				$result = array(
					"code" => "100",
					"msg" => "获取成功",
					"user" => $user,
					"email" => $email
				);
			}
		}else{
			$result = array(
				"code" => "103",
				"msg" => "获取时发生错误"
			);
		}
	}
}else{
	$result = array(
		"code" => "102",
		"msg" => "未登录"
	);
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>