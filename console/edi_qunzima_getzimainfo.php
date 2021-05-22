<?php
header("Content-type:application/json");
session_start();
if(isset($_SESSION["huoma.admin"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获取数据
	$zmid = $_GET["zmid"];

	if(empty($zmid)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 获取当前zmid的详细内容
		$sql_zminfo = "SELECT * FROM huoma_qunzima WHERE zmid = '$zmid'";
		$result_zminfo = $conn->query($sql_zminfo);
		if ($result_zminfo->num_rows > 0) {
			while($row_zminfo = $result_zminfo->fetch_assoc()) {
				$yuzhi = $row_zminfo["yuzhi"];
				$qrcode = $row_zminfo["qrcode"];
				$dqdate = $row_zminfo["dqdate"];
			}
			$result = array(
				"code" => "100",
				"msg" => "获取成功",
				"yuzhi" => $yuzhi,
				"qrcode" => $qrcode,
				"dqdate" => $dqdate
			);
		}else{
			$result = array(
				"code" => "103",
				"msg" => "获取失败"
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