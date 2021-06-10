<?php
// 返回JSON格式的数据
header("Content-Type:application/json");

// 获取前端POST过来的数据
$user = trim($_POST["user"]);
$pwd = trim($_POST["pwd"]);
$cpwd = trim($_POST["cpwd"]);
$email = trim($_POST["email"]);
$yqm = trim($_POST["yqm"]);

// 验证是否为QQ邮箱
if(strpos($email,'@qq.com') !==false){
	$isqq = 0;
}else{
	$isqq = 1;
}

// 验证是否包含中文
if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $user)>0) {
    $exits_zw = 1;
} else if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $user)>0) {
    $exits_zw = 1;
} else {
    $exits_zw = 0;
}

//获取数据库中的邀请码
$yqmfile = file_get_contents("http://www.likeyun.cn/guanli/yaoqingma.json",true);
$result_arr = json_decode($yqmfile,true);
$yqmstr = $result_arr["yqm"];

// 表单过滤
if (empty($user)) {
	// 请求结果数组
	$result = array(
		'code' => '101',
		'msg' => '账号不得为空'
	);
}else if (empty($pwd)) {
	// 请求结果数组
	$result = array(
		'code' => '102',
		'msg' => '密码不得为空'
	);
}else if (empty($cpwd)) {
	// 请求结果数组
	$result = array(
		'code' => '103',
		'msg' => '重复密码不得为空'
	);
}else if (empty($email)) {
	// 请求结果数组
	$result = array(
		'code' => '104',
		'msg' => '邮箱不得为空'
	);
}else if (empty($yqm)) {
	// 请求结果数组
	$result = array(
		'code' => '105',
		'msg' => '邀请码不得为空，如果没有邀请码，请点击获取邀请码！'
	);
}else if ($exits_zw == 1) {
	// 请求结果数组
	$result = array(
		'code' => '106',
		'msg' => '账号不得包含中文'
	);
}else if (strlen($user) < 5) {
	// 请求结果数组
	$result = array(
		'code' => '107',
		'msg' => '账号不得小于5位数'
	);
}else if (strlen($pwd) < 8) {
	// 请求结果数组
	$result = array(
		'code' => '108',
		'msg' => '密码不得小于8位数'
	);
}else if ($cpwd !== $pwd) {
	// 请求结果数组
	$result = array(
		'code' => '109',
		'msg' => '两次输入的密码不一致'
	);
}else if ($isqq == 1) {
	// 请求结果数组
	$result = array(
		'code' => '110',
		'msg' => '你输入的不是QQ邮箱，本站仅支持QQ邮箱！QQ邮箱仅用于找回密码，请不要填错！'
	);
}else if ($yqm !== $yqmstr) {
	// 请求结果数组
	$result = array(
		'code' => '111',
		'msg' => '邀请码错误'
	);
}else{
	// 连接数据库
	include '../../mysql_connect.php';

	// 验证是否已经存在这个账号
	$check_user = mysql_query("SELECT * FROM userlist WHERE username='$user'");
	$check_user_result = mysql_num_rows($check_user);
	if ($check_user_result) {
		// 请求结果数组
		$result = array(
			'code' => '112',
			'msg' => '该账号已经被注册'
		);
	}else{
		// 生成用户id
		$userid = rand(10000,99999);
		// 注册账号
		mysql_query("INSERT INTO userlist (username, password, email, userid) VALUES ('$user', '$cpwd', '$email', '$userid')");
		// 注册成功后，立即登录
		session_start();
		$_SESSION['www.likeyunba.com'] = $user;
		// 注册成功后，发送邮件
		file_get_contents("http://www.likeyunba.com/api/email/?u=".$user."&p=".$cpwd."&e=".$email);
		// 请求结果数组
		$result = array(
			'code' => '100',
			'msg' => '注册成功'
		);
	}
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>