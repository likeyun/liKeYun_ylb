<?php

// 返回的是json
header("Content-type:application/json");

// 引入配置文件
include '../../db_config/db_config.php';

// 获取order_no
$order_no = trim($_GET["order_no"]);

// 查询数据库是否存在该订单
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
$sql_check_order = "SELECT * FROM huoma_order WHERE order_no = '$order_no'";
$result_check_order = $conn->query($sql_check_order);

// 判断支付状态
if ($result_check_order->num_rows > 0) {
	// 订单存在
	$result = array(
		'code' => '200', 
		'msg' => '支付成功'
	);
}else{
	// 订单不存在
	$result = array(
		'code' => '201',
		'msg' => '未支付'
	);
}

// 断开数据库连接
$conn->close();

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>