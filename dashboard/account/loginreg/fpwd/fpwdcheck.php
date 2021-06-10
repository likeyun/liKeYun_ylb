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
	include '../../mysql_connect.php';

	$check_user = mysql_query("SELECT * FROM userlist WHERE username='$user'");
	$check_user_result = mysql_num_rows($check_user);
	if ($check_user_result) {
		// 账号验证成功，下一步验证邮箱
		$check_email = mysql_query("SELECT * FROM userlist WHERE email='$email' AND username='$user'");
		$check_email_result = mysql_num_rows($check_email);
		if ($check_email_result) {
			// 查询密码
			while ($row_pwd = mysql_fetch_array($check_email)) {
				$pwdstr = $row_pwd["password"];
			}
			// 请求结果数组
			$result = array(
				'code' => '100',
				'msg' => '账号和邮箱验证成功！您的密码是：'.$pwdstr
			);
		}else{
			// 请求结果数组
			$result = array(
				'code' => '104',
				'msg' => '邮箱与账号不对应'
			);
		}
	}else{
		// 请求结果数组
		$result = array(
			'code' => '103',
			'msg' => '账号错误'
		);
	}
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>