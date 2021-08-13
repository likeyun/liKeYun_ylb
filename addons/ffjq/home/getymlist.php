<?php
header("Content-type:application/json");
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获取数据
	$ymtype = $_GET["ymtype"];

	if(empty($ymtype)){
		$ymlist = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 获取域名列表
		$sql_ymlist = "SELECT * FROM huoma_yuming WHERE ym_type = '$ymtype'";
		$result_ymlist = $conn->query($sql_ymlist);
		if ($result_ymlist->num_rows > 0) {
			// 将所有结果保存到数组
			$ymlist = array();
			while($row_ymlist = $result_ymlist->fetch_assoc()) {
				$ymlist[] = $row_ymlist;
			}
		}else{
			$ymlist = array(
				"code" => "103",
				"msg" => "获取失败"
			);
		}
	}
}else{
	$ymlist = array(
		"code" => "102",
		"msg" => "未登录"
	);
}
echo json_encode($ymlist,JSON_UNESCAPED_UNICODE);
?>