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
	$yqm = $_GET["yqm"];

	if(empty($yqm)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 获取当前状态
		$sql_yqm_status = "SELECT * FROM huoma_yqm WHERE yqm = '$yqm'";
		$result_yqm_status = $conn->query($sql_yqm_status);
		if ($result_yqm_status->num_rows > 0) {
			while($row_yqm_status = $result_yqm_status->fetch_assoc()) {
				$yqm_status = $row_yqm_status["yqm_status"];
				if ($yqm_status == 2) {
					// 更新数据库
					$update_sql = "UPDATE huoma_yqm SET yqm_status='1' WHERE yqm='$yqm'";
					if ($conn->query($update_sql) === TRUE) {
						$result = array(
							"code" => "100",
							"msg" => "已恢复"
						);
					}else{
						$result = array(
							"code" => "101",
							"msg" => "数据库发生错误"
						);
					}
					
				}else if ($yqm_status == 1) {
					// 更新数据库
					$update_sql = "UPDATE huoma_yqm SET yqm_status='2' WHERE yqm='$yqm'";
					if ($conn->query($update_sql) === TRUE) {
						$result = array(
							"code" => "100",
							"msg" => "已停用"
						);
					}else{
						$result = array(
							"code" => "101",
							"msg" => "数据库发生错误"
						);
					}
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