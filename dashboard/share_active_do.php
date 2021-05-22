<?php
header("Content-type:application/json");
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获取数据
	$active_id = $_GET["activeid"];

	if(empty($active_id)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 获取落地页域名
		$sql_yuming = "SELECT * FROM huoma_active WHERE active_id = '$active_id'";
		$result_yuming = $conn->query($sql_yuming);
		if ($result_yuming->num_rows > 0) {
			while($row_yuming = $result_yuming->fetch_assoc()) {
				$active_yuming = $row_yuming["active_yuming"];
				// 生成网址
				$SERVER = $active_yuming.$_SERVER["REQUEST_URI"];
				$url = dirname(dirname($SERVER))."/common/active/?activeid=".$active_id;
				$result = array(
					"code" => "100",
					"msg" => "分享成功",
					"url" => $url
				);
			}
		}else{
			$result = array(
				"code" => "103",
				"msg" => "分享发生错误"
			);
		}
	}
	// 返回结果
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
}else{
	$result = array(
		"code" => "102",
		"msg" => "未登录"
	);
	// 未登录
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
}
?>