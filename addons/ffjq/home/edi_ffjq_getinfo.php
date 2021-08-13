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
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 获取当前id的详细内容
		$sql_ffqinfo = "SELECT * FROM huoma_addons_ffjq WHERE ffjq_id = '$ffjq_id'";
		$result_ffqinfo = $conn->query($sql_ffqinfo);
		if ($result_ffqinfo->num_rows > 0) {
			while($row_ffqinfo = $result_ffqinfo->fetch_assoc()) {
				$ffjq_title = $row_ffqinfo["ffjq_title"];
				$ffjq_price = $row_ffqinfo["ffjq_price"];
				$ffjq_rkym = $row_ffqinfo["ffjq_rkym"];
				$ffjq_ldym = $row_ffqinfo["ffjq_ldym"];
				$ffjq_qrcode = $row_ffqinfo["ffjq_qrcode"];
			}
			$result = array(
				"code" => "100",
				"msg" => "获取成功",
				"ffjq_title" => $ffjq_title,
				"ffjq_price" => $ffjq_price,
				"ffjq_rkym" => $ffjq_rkym,
				"ffjq_ldym" => $ffjq_ldym,
				"ffjq_qrcode" => $ffjq_qrcode

			);
		}else{
			$result = array(
				"code" => "103",
				"msg" => "获取失败，数据库发生错误"
			);
		}
	}
}else{
	$result = array(
		"code" => "102",
		"msg" => "未登录"
	);
}

// 结果返回JSON
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>