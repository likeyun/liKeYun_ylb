<?php
header("Content-type:application/json");
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获取数据
	$ffjq_id = $_GET["ffqid"];

	if(empty($ffjq_id)){
		$result = array(
			"result" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 获取落地页域名
		$sql_yuming = "SELECT ffjq_rkym FROM huoma_addons_ffjq WHERE ffjq_id = '$ffjq_id'";
		$result_yuming = $conn->query($sql_yuming);
		if ($result_yuming->num_rows > 0) {
			while($row_yuming = $result_yuming->fetch_assoc()) {
				$ffjq_rkym = $row_yuming["ffjq_rkym"]; // 入口域名

				// 生成入口链接
				$SERVER = $ffjq_rkym.$_SERVER["REQUEST_URI"];
				$rkurl = dirname($SERVER)."/redirect/?ffqid=".$ffjq_id;

				// 反馈结果
				$result = array(
					"result" => "100",
					"msg" => "分享成功",
					"rkurl" => $rkurl
				);
			}
		}else{
			$result = array(
				"result" => "103",
				"msg" => "分享发生错误"
			);
		}
	}
	// 返回结果
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
}else{
	$result = array(
		"result" => "102",
		"msg" => "未登录"
	);
	// 未登录
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
}
?>