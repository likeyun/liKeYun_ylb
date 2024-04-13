<?php 
use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception; 

require './Exception.php'; 
require './PHPMailer.php'; 
require './SMTP.php'; 

$noti_text = $_GET['noti_text'];

// 安全码（只能纯数字）
// 这个安全码需要跟console/public/sendNotification.php/里面的第48行代码的一致
// 如需修改，请前往上述路径修改
$aqm = 123456;

// 获取传过来的安全码
$getAnquanMa = intval($_GET['aqm']);

// 过滤风险请求
if(intval($getAnquanMa) !== $aqm){
    
    // 如果安全码不一样
    // 停止运行
    echo '不安全的请求，已被拦截！';
    exit;
}

// 从通知渠道配置中获取配置信息
include '../../Db.php';
$db = new DB_API($config);
$getEmailNotificationConfig = $db->set_table('huoma_notification')->find();

if($getEmailNotificationConfig){
    
    // email_acount
    $noti_email_acount = json_decode(json_encode($getEmailNotificationConfig))->email_acount;
    
    // email_pwd
    $noti_email_pwd = json_decode(json_encode($getEmailNotificationConfig))->email_pwd;
    
    // email_receive
    $noti_email_receive = json_decode(json_encode($getEmailNotificationConfig))->email_receive;
    
    // SMTP服务器
    $noti_email_smtp = json_decode(json_encode($getEmailNotificationConfig))->email_smtp;
    
    // 邮件服务器端口
    $noti_email_port = json_decode(json_encode($getEmailNotificationConfig))->email_port;
}

$mail = new PHPMailer(true);
try { 
    
    //服务器配置 
    $mail->CharSet ="UTF-8";                     //设定邮件编码 
    $mail->SMTPDebug = 0;                        // 调试模式输出 
    $mail->isSMTP();                             // 使用SMTP 
    $mail->Host = $noti_email_smtp;                // SMTP服务器 
    $mail->SMTPAuth = true;                      // 允许 SMTP 认证 
    $mail->Username = $noti_email_acount;          // SMTP 用户名  即邮箱的用户名 
    $mail->Password = $noti_email_pwd;             // SMTP 密码  部分邮箱是授权码(例如163邮箱) 
    $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议 
    $mail->Port = $noti_email_port;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持 

    $mail->setFrom($noti_email_acount, '引流宝系统通知');  //发件人 
    $mail->addAddress($noti_email_receive, 'You');  // 收件人 
    //$mail->addAddress('ellen@example.com');  // 可添加多个收件人 
    $mail->addReplyTo($noti_email_acount, '引流宝系统通知'); //回复的时候回复给哪个邮箱 建议和发件人一致 
    //$mail->addCC('cc@example.com');                    //抄送 
    //$mail->addBCC('bcc@example.com');                    //密送 

    //发送附件 
    // $mail->addAttachment('../xy.zip');         // 添加附件 
    // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名 

    //Content 
    $mail->isHTML(true); // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容 
    $mail->Subject = '点进来查看'; 
    
    // HTML内容
    $mail->Body    = '<h3 style="padding:30px 20px;background:rgba(59,94,225,0.1);border-radius:10px;">'.$noti_text.'</h3>' . date('Y-m-d H:i:s'); 
    $mail->AltBody = '如果邮件客户端不支持HTML则显示此内容'; 

    $mail->send(); 
    echo '邮件发送成功'; 
    
} catch (Exception $e) { 
    
    echo '邮件发送失败: ', $mail->ErrorInfo; 
}