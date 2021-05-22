<!DOCTYPE html>
<html>
<head>
  <title>里客云开源活码系统用户管理后台</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdn.staticfile.org/popper.js/1.15.0/umd/popper.min.js"></script>
  <script src="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.huoma.css">
</head>
<body>

<!-- 全局信息提示框 -->
<div id="Result" style="display: none;"></div>

<?php
// 页面字符编码
header("Content-type:text/html;charset=utf-8");
// 判断登录状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

  // 数据库配置
  include '../db_config/db_config.php';
  include '../db_config/VersionCheck.php';

  // 创建连接
  $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title"><a href="./">活码系统管理后台</a></span>
  <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.dashboard"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">';
  if ($version !== $v_str_v) {
    echo '<br/>
    <div class="alert alert-warning">
      <strong>'.$v_str_m.'<a href="'.$v_str_u.'">点击更新</a></strong>
    </div>';
  }
  echo '<br/>
  <h3>活码管理后台 / 系统设置 / 邮件服务</h3> 
  <p>设置邮件服务器，配置发送邮件参数</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">邮件服务</button>
    <a href="./set.php"><button type="button" class="btn btn-light">返回上一页</button></a>
    <a href="./index.php"><button type="button" class="btn btn-light">返回首页</button></a>
  </div>';

  // 获取设置参数
  $sql_setval = "SELECT * FROM huoma_set";
  $result_setval = $conn->query($sql_setval);

  if ($result_setval->num_rows > 0) {
    // 如果有参数
    while($row_setval = $result_setval->fetch_assoc()) {
      $email_smtpserver = $row_setval["email_smtpserver"];
      $email_smtpserverport = $row_setval["email_smtpserverport"];
      $email_smtpusermail = $row_setval["email_smtpusermail"];
      $email_smtpuser = $row_setval["email_smtpuser"];
      $email_smtppass = $row_setval["email_smtppass"];
    }

    echo '<!-- 右侧布局 -->
  <form onsubmit="return false" id="setval">
  <div class="right-nav">

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">SMTP服务器</span>
      </div>
      <input type="text" class="form-control" placeholder="SMTP服务器，在邮箱管理后台获取" value="'.$email_smtpserver.'" name="email_smtpserver">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">SMTP服务器端口</span>
      </div>
      <input type="text" class="form-control" placeholder="SMTP服务器端口，在邮箱管理后台获取" value="'.$email_smtpserverport.'" name="email_smtpserverport">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">SMTP服务器的用户邮箱</span>
      </div>
      <input type="text" class="form-control" placeholder="SMTP服务器的用户邮箱，你要发送邮件的邮箱" value="'.$email_smtpusermail.'" name="email_smtpusermail">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">SMTP服务器的用户帐号</span>
      </div>
      <input type="text" class="form-control" placeholder="SMTP服务器的用户帐号" value="'.$email_smtpuser.'" name="email_smtpuser">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">SMTP服务器的授权密码</span>
      </div>
      <input type="text" class="form-control" placeholder="SMTP服务器的授权密码，在邮箱后台获取" value="'.$email_smtppass.'" name="email_smtppass">
    </div>
    
    <button type="submit" class="btn btn-dark" onclick="setval();">提交设置</button><br/><br/>
    <a href="https://mp.weixin.qq.com/s?__biz=MzU2NzIyMzA1Mw==&mid=100000290&idx=1&sn=b209f616d2cb1228e4820c37b2d2136d&chksm=7ca134404bd6bd56d2ea2e2fe17db450964d7e941f953d8cb42e251b52c9a3c9fa77761b3a5d#rd" target="blank">如何设置？以上参数如何获得？</a>
  </div>
  </form>';
  }else{
    // 如果没有参数
  }

}else{
  // 跳转到登陆界面
  header("Location:../LoginReg/Login.html");
}
?>

</div>

<script>
// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}

// 设置支付接口
function setval(){
  $.ajax({
      type: "POST",
      url: "./email_set_do.php",
      data: $('#setval').serialize(),
      success: function (data) {
        // 设置成功
        if (data.code==100) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 刷新列表
          location.reload();
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 设置失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}

</script>
</body>
</html>