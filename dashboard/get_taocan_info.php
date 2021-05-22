<?php
header("Content-type:application/json");
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获取数据
	$tc_id = $_GET["tcid"];

	if(empty($tc_id)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 获取用户信息
		$sql_taocan_info = "SELECT * FROM huoma_taocan WHERE tc_id = '$tc_id'";
		$result_taocan_info = $conn->query($sql_taocan_info);
		if ($result_taocan_info->num_rows > 0) {
			while($row_taocan_info = $result_taocan_info->fetch_assoc()) {
				$tc_title = $row_taocan_info["tc_title"];
				$tc_days = $row_taocan_info["tc_days"];
				$tc_price = $row_taocan_info["tc_price"];

				$result = array(
					"code" => "100",
					"msg" => "获取成功",
					"tc_title" => $tc_title,
					"tc_days" => $tc_days,
					"tc_price" => $tc_price
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