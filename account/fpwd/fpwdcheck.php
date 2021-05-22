<?php
// 返回JSON格式的数据
header("Content-Type:application/json");

// 获取前端POST过来的数据
$user = trim($_POST["user"]);
$email = trim($_POST["email"]);

// 过滤表单
if (empty($user)) {
	// 请求结果数组
	$result = array(
		'code' => '101',
		'msg' => '账号未填'
	);
}else if (empty($email)) {
	// 请求结果数组
	$result = array(
		'code' => '102',
		'msg' => '邮箱未填'
	);
}else{
	// 连接数据库
	include '../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
	 
	$check_user = "SELECT * FROM huoma_user WHERE user='$user' AND email='$email'";
	$result_user = $conn->query($check_user);
	
	// 验证结果
	if ($result_user->num_rows > 0) {
	    // 账号、邮箱正确
	    // 获取密码
	    while($row_userinfo = $result_user->fetch_assoc()) {
			$pwd = $row_userinfo["pwd"];
		}
		$sendurl = dirname(dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"])).'/email/send_pwd.php?email='.$email.'&pwd='.$pwd;
		file_get_contents($sendurl);
		$result = array(
			'code' => '100',
			'msg' => '找回成功，已发送到你的邮箱，请注意查收！'
		);
	} else {
	    $result = array(
			'code' => '103',
			'msg' => '账号或邮箱错误'
		);
	}
	// 断开数据库连接
	$conn->close();
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>