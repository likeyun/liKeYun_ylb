<?php
// 返回JSON格式的数据
header("Content-Type:application/json");

// 获取前端POST过来的数据
$user = trim($_POST["user"]);
$pwd = trim($_POST["pwd"]);

// 过滤表单
if (empty($user)) {
	// 请求结果数组
	$result = array(
		'code' => '101',
		'msg' => '账号未填'
	);
}else if (empty($pwd)) {
	// 请求结果数组
	$result = array(
		'code' => '102',
		'msg' => '密码未填'
	);
}else{
	// 连接数据库
	include '../../mysql_connect.php';

	$check_user = mysql_query("SELECT * FROM userlist WHERE username='$user' AND password='$pwd'");
	$check_user_result = mysql_num_rows($check_user);
	if ($check_user_result) {
		// 请求结果数组
		$result = array(
			'code' => '100',
			'msg' => '登录成功'
		);
		session_start();
		$_SESSION['www.likeyunba.com'] = $user;
	}else{
		// 请求结果数组
		$result = array(
			'code' => '103',
			'msg' => '账号或密码错误'
		);
	}
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>