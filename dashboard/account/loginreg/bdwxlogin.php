<?php
//定义header，返回json
header("Content-type:allication/json");

//获得POST过来的数据
$user = $_POST["username"];
$pass = $_POST["password"];
$open = $_POST["openid"];

//过滤
$username = trim($user);
$password = trim($pass);
$openid = trim($open);

//判断是否为空
if (empty($username)) {

	//10001 用户名为空
	echo "[{\"result\":\"10001\"}]";

}else if (empty($password)) {

	//10002 密码为空
	echo "[{\"result\":\"10002\"}]";

}else{

	//获得数据库连接配置
	require_once("../mysql_connect.php");

	//验证是否存在该用户
	$getuser = mysql_query("SELECT * FROM userlist WHERE username = '$username'");
	$user_exits = mysql_num_rows($getuser);
	if ($user_exits) {

		//如果存在该用户，则验证密码
		//获取该账号的密码
		while ($pwd_rows = mysql_fetch_array($getuser)) {
			$pwd = $pwd_rows["password"];
		}

		//如果密码正确，则进行绑定
		if ($pwd == $password) {

			//绑定操作
			mysql_query("UPDATE userlist SET m_openid = '$openid' WHERE username = '$username' AND password = '$password'");

			//10000 绑定成功
			echo "[{\"result\":\"10000\"}]";

		}else{
			
			//10004 绑定失败，密码错误
			echo "[{\"result\":\"10004\"}]";
		}

	}else{

		//如果不存在该用户，则返回绑定失败
		//10003 绑定失败，账号未注册
		echo "[{\"result\":\"10003\"}]";
	}
	
}
?>