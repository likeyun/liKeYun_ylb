<?php
// 字符编码是json
header("Content-type:application/json");

// 验证登录状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	$del_sql = "truncate table huoma_yqm";
	if ($conn->query($del_sql) === TRUE) {
		// 返回结果
		$result = array(
			"code" => "100",
			"msg" => "已清空"
		);
	}else{
		// 返回结果
		$result = array(
			"code" => "101",
			"msg" => "清空失败"
		);
	}
}else{
	$result = array(
		"code" => "102",
		"msg" => "未登录"
	);
}

// 输出json格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>