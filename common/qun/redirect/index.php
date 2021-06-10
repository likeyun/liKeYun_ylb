<?php
header("Content-type:text/html;charset=utf-8");

// 数据库配置
include '../../../db_config/db_config.php';

// 创建连接
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

// 获取数据
$qun_hmid = trim($_GET["hmid"]);

if(empty($qun_hmid)){
	echo '非法请求';
}else{
	// 获取落地页域名
	$sql_yuming = "SELECT * FROM huoma_qun WHERE qun_hmid = '$qun_hmid'";
	$result_yuming = $conn->query($sql_yuming);
	if ($result_yuming->num_rows > 0) {
		while($row_yuming = $result_yuming->fetch_assoc()) {
			$qun_ldym = $row_yuming["qun_ldym"]; // 落地域名

			// 生成落地链接
			$SERVER = $qun_ldym.$_SERVER["REQUEST_URI"];
			$ldurl = dirname(dirname($SERVER))."/?hmid=".$qun_hmid;

			// 跳转到落地页
			header('Location:'.$ldurl);
		}
	}else{
		echo '该活码不存在';
	}
}
?>