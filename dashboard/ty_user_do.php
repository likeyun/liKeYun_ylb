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
	$user_id = $_GET["userid"];

	if(empty($user_id)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 获取当前状态
		$sql_user_status = "SELECT * FROM huoma_user WHERE user_id = '$user_id'";
		$result_user_status = $conn->query($sql_user_status);
		if ($result_user_status->num_rows > 0) {
			while($row_user_status = $result_user_status->fetch_assoc()) {
				$user_status = $row_user_status["user_status"];
				if ($user_status == 2) {
					// 更新数据库
					mysqli_query($conn,"UPDATE huoma_user SET user_status='1' WHERE user_id=".$user_id);
					$result = array(
						"code" => "100",
						"msg" => "已恢复"
					);
				}else if ($user_status == 1) {
					// 更新数据库
					mysqli_query($conn,"UPDATE huoma_user SET user_status='2' WHERE user_id=".$user_id);
					$result = array(
						"code" => "100",
						"msg" => "已停用"
					);
				}
			}
		}else{
			$result = array(
				"code" => "106",
				"msg" => "参数错误"
			);
		}
	}
}else{
	$result = array(
		"code" => "104",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>