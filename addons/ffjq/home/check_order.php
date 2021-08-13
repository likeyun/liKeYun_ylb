<?php

// 返回json格式的数据
header("Content-type:application/json");

// 数据库配置
include '../../../db_config/db_config.php';

// 创建连接
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

// 获得表单POST过来的数据
$order = trim($_GET["order"]);

$sql_checkorder = "SELECT * FROM huoma_addons_ffjq_order WHERE ffjq_order='$order'";
$result_checkorder = $conn->query($sql_checkorder);
if ($result_checkorder->num_rows > 0) {
	while($row = $result_checkorder->fetch_assoc()) {
        $bzcode = $row["ffjq_bzcode"];
    }
    $result = array(
    	'code' => '100',
    	'msg' => '支付成功',
    	'bzcode' => $bzcode
    );
} else {
    $result = array(
    	'code' => '101',
    	'msg' => '未支付'
    );
}

$conn->close();

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>