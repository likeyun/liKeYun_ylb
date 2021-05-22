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
	$qun_hmid = $_GET["hmid"];

	if(empty($qun_hmid)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 获取当前状态
		$sql_qun_status = "SELECT * FROM huoma_qun WHERE qun_hmid = '$qun_hmid'";
		$result_qun_status = $conn->query($sql_qun_status);
		if ($result_qun_status->num_rows > 0) {
			while($row_qun_status = $result_qun_status->fetch_assoc()) {
				$qun_status = $row_qun_status["qun_status"];
				if ($qun_status == 3) {
					// 更新数据库
					mysqli_query($conn,"UPDATE huoma_qun SET qun_status='1' WHERE qun_hmid=".$qun_hmid);
					$result = array(
						"code" => "100",
						"msg" => "已启用"
					);
				}else{
					// 更新数据库
					mysqli_query($conn,"UPDATE huoma_qun SET qun_status='3' WHERE qun_hmid=".$qun_hmid);
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