<?php
header("Content-type:text/html;charset=utf-8");

// 数据库配置
include '../../../../db_config/db_config.php';

// 创建连接
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

// 获取数据
$ffjq_id = trim($_GET["ffqid"]);

if(empty($ffjq_id)){
	echo '非法请求';
}else{
	// 获取落地页域名
	$sql_yuming = "SELECT ffjq_ldym FROM huoma_addons_ffjq WHERE ffjq_id = '$ffjq_id'";
	$result_yuming = $conn->query($sql_yuming);
	if ($result_yuming->num_rows > 0) {
		while($row_yuming = $result_yuming->fetch_assoc()) {
			$ffjq_ldym = $row_yuming["ffjq_ldym"]; // 落地域名

			// 生成落地链接
			$SERVER = $ffjq_ldym.$_SERVER["REQUEST_URI"];
			$ldurl = dirname(dirname($SERVER))."/ffq.php?ffqid=".$ffjq_id."&token=".md5($ffjq_id)."&lang=zh_CN&charset=utf-8";
			// echo $ldurl;
			// 跳转到落地页
			header('Location:'.$ldurl);
		}
	}else{
		echo '该活码不存在';
	}
}
?>