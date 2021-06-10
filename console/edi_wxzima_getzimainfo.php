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
		$sql_zminfo = "SELECT * FROM huoma_wxzima WHERE zmid = '$zmid'";
		$result_zminfo = $conn->query($sql_zminfo);
		if ($result_zminfo->num_rows > 0) {
			while($row_zminfo = $result_zminfo->fetch_assoc()) {
				$qrcode = $row_zminfo["qrcode"];
				$zima_status = $row_zminfo["zima_status"];
				$wx_num = $row_zminfo["wx_num"];
				$wx_beizhu = $row_zminfo["wx_beizhu"];
				$wx_yuzhi = $row_zminfo["wx_yuzhi"];
			}
			$result = array(
				"code" => "100",
				"msg" => "获取成功",
				"qrcode" => $qrcode,
				"zima_status" => $zima_status,
				"wx_num" => $wx_num,
				"wx_beizhu" => $wx_beizhu,
				"wx_yuzhi" => $wx_yuzhi
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