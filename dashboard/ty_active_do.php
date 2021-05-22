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
	$active_id = $_GET["activeid"];

	if(empty($active_id)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 获取当前状态
		$sql_active_status = "SELECT * FROM huoma_active WHERE active_id = '$active_id'";
		$result_active_status = $conn->query($sql_active_status);
		if ($result_active_status->num_rows > 0) {
			while($row_active_status = $result_active_status->fetch_assoc()) {
				$active_status = $row_active_status["active_status"];
				if ($active_status == 3) {
					// 更新数据库
					mysqli_query($conn,"UPDATE huoma_active SET active_status='1' WHERE active_id=".$active_id);
					$result = array(
						"code" => "100",
						"msg" => "已启用"
					);
				}else{
					// 更新数据库
					mysqli_query($conn,"UPDATE huoma_active SET active_status='3' WHERE active_id=".$active_id);
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