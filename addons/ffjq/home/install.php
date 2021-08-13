<?php
// 返回JSON
header("Content-type:application/json");

// 引入数据库配置
include '../../../db_config/db_config.php';
 
// 创建连接
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
// 检测连接
if ($conn->connect_error) {
	$result = array('code'=>'101','msg'=>'数据库连接失败');
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	exit;
} 

// 创建数据表huoma_addons_ffjq
$huoma_addons_ffjq = "CREATE TABLE huoma_addons_ffjq (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
ffjq_id VARCHAR(30) NULL,
ffjq_title VARCHAR(30) NULL,
ffjq_creatdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ffjq_pv VARCHAR(30) DEFAULT '0',
ffjq_rkym TEXT(300) NULL,
ffjq_ldym TEXT(300) NULL,
ffjq_price VARCHAR(30) NULL,
ffjq_status VARCHAR(30) DEFAULT '1',
ffjq_qrcode TEXT(300) NULL,
ffjq_user VARCHAR(30) NULL
)";

// 创建数据表huoma_addons_ffjq_order
$huoma_addons_ffjq_order = "CREATE TABLE huoma_addons_ffjq_order (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
ffjq_id VARCHAR(30) NULL,
ffjq_title VARCHAR(30) NULL,
ffjq_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ffjq_order VARCHAR(30) NULL,
ffjq_price VARCHAR(30) NULL,
ffjq_bzcode VARCHAR(30) NULL,
ffjq_openid VARCHAR(30) NULL
)";

// 判断安装结果
if ($conn->query($huoma_addons_ffjq) === TRUE && $conn->query($huoma_addons_ffjq_order) === TRUE) {
    $result = array('code'=>'100','msg'=>'创建成功');
} else {
	$result = array('code'=>'102','msg'=>'创建数据表错误'.$conn->error);
}

// 断开数据库连接
$conn->close();

// 返回JSON数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>