<?php
header("Content-type:text/html;charset=utf-8");

// 引入配置_emailinfo
include './Smtp.class.php';
include '../../db_config/db_config.php';

// 创建连接
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

// 获取群活码列表
$sql_emailinfo = "SELECT * FROM huoma_set WHERE id='1'";
$result_emailinfo = $conn->query($sql_emailinfo);

if ($result_emailinfo->num_rows > 0) {
	while($row_emailinfo = $result_emailinfo->fetch_assoc()) {
		$email_smtpserver = $row_emailinfo["email_smtpserver"];
		$email_smtpserverport = $row_emailinfo["email_smtpserverport"];
		$email_smtpusermail = $row_emailinfo["email_smtpusermail"];
		$email_smtpuser = $row_emailinfo["email_smtpuser"];
		$email_smtppass = $row_emailinfo["email_smtppass"];
	}
}else{
	echo "邮件服务器不存在";
}

$smtpserver = $email_smtpserver; // SMTP服务器
$smtpserverport = $email_smtpserverport; // SMTP服务器端口
$smtpusermail = $email_smtpusermail; // SMTP服务器的用户邮箱
$smtpuser = $email_smtpuser; // SMTP服务器的用户帐号
$smtppass = $email_smtppass; // SMTP服务器的授权密码
$smtp = new Smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
$emailtype = "HTML";
$smtpemailto = $_GET['email'];
$smtpemailfrom = $smtpusermail;
$user = $_GET["user"];
$pwd = $_GET["pwd"];
$emailsubject = "注册成功！";
$emailbody = "<p>你的账号是：".$user."</p><p>你的密码是：".$pwd."</p><p>发送时间：".date("Y-m-d h:i:s")."</p><p>里客云科技</p><p><a href='http://www.likeyuns.com'>www.likeyuns.com</a></p>";
// 发送邮件
$smtp->sendmail($smtpemailto, $smtpemailfrom, $emailsubject, $emailbody, $emailtype);
?>

<!-- 发件人昵称在Smtp.class.php第86行，自行修改 -->