<?php
// 设置页面返回的字符编码为json格式
header("Content-type:application/json");

// 开启session，验证登录状态
session_start();
if(isset($_SESSION["huoma.admin"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$zmid = $_GET["zmid"];

	if(empty($zmid)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 获取当前状态
		$sql_zima_status = "SELECT * FROM huoma_qunzima WHERE zmid = '$zmid'";
		$result_zima_status = $conn->query($sql_zima_status);
		if ($result_zima_status->num_rows > 0) {
			while($row_zima_status = $result_zima_status->fetch_assoc()) {
				$zima_status = $row_zima_status["zima_status"];
				$qrcode = $row_zima_status["qrcode"];
				if ($zima_status == 1) {
					// 更新数据库
					mysqli_query($conn,"UPDATE huoma_qunzima SET zima_status='2' WHERE zmid=".$zmid);
					$result = array(
						"code" => "100",
						"msg" => "已关闭"
					);
				}else if ($zima_status == 2){
					// 验证是否有二维码
					if ($qrcode == '') {
						$result = array(
							"code" => "107",
							"msg" => "请上传二维码后再开启"
						);
					}else{
						// 更新数据库
						mysqli_query($conn,"UPDATE huoma_qunzima SET zima_status='1' WHERE zmid=".$zmid);
						$result = array(
							"code" => "100",
							"msg" => "已开启"
						);
					}
				}else{
					$result = array(
						"code" => "105",
						"msg" => "发生错误"
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