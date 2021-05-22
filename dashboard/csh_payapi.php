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

	// 创建支付接口
	// 字符编码设为utf8
	mysqli_query($conn, "SET NAMES UTF-8");
	$sql_creat_payapi = "INSERT INTO huoma_payselect (payapi, payselect, paytype, paytitle) VALUES ('no_wxpay','2','wx','未开通'),('no_alipay','2','ali','未开通'),('payjs_wxpay','1','wx','PayJs微信支付'),('payjs_alipay','1','ali','PayJs支付宝'),('xdd_wxpay','1','wx','小叮当微信支付'),('xdd_alipay','1','ali','小叮当支付宝'),('dmf_alipay','1','ali','支付宝当面付')";
	
	if ($conn->query($sql_creat_payapi) === TRUE) {
		$result = array(
			"code" => "100",
			"msg" => "初始化完成"
		);
	}else{
		$result = array(
			"code" => "103",
			"msg" => "初始化失败"
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