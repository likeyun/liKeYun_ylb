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
  <h3>活码管理后台 / 系统设置 / 支付接口 / 支付宝当面付</h3> 
  <p>配置支付接口，设置支付API</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-light"><a href="./payset.php">支付选项</a></button>
    <button type="button" class="btn btn-dark">支付宝当面付</button>
    <button type="button" class="btn btn-light"><a href="./xdd_set.php">小叮当支付</a></button>
    <button type="button" class="btn btn-light"><a href="./payjs_set.php">PayJs</a></button>
    <button type="button" class="btn btn-light"><a href="./set.php">返回上一页</a></button>
    <button type="button" class="btn btn-light"><a href="./index.php">返回首页</a></button>
  </div>';


  echo '<!-- 右侧布局 -->
  <div class="right-nav">
    
    <div class="jumbotron">
      <h4>支付宝当面付配置教程（文档更新时间：2021-04-30）</h4>
      <br/>
      <p>1、请打开本程序根目录下的 pay/alipay/config.php</p>
      <p>2、把你自己的当面付appid、notifyUrl、rsaPrivateKey、alipayPublicKey都填写进去即可</p>
      <h5><span class="badge badge-primary">appid获取方法</span></h5>
      <p>(1)、访问链接 https://open.alipay.com/platform/home.htm 登陆支付宝开放平台</p>
      <p><img src="../pay/alipay/step/1.png"/></p>
      <p><img src="../pay/alipay/step/2.png"/></p>
      <h5><span class="badge badge-primary">notifyUrl设置</span></h5>
      <p>notifyUrl是 pay/alipay/notify.php在线上服务器对应的URL，例如你的域名是http://www.baidu.com，你的活码系统部署在根目录下的huoma目录，那么notifyUrl应该填写的链接是：http://www.baidu.com/huoma/pay/alipay/notify.php</p>
      <p>除了要在pay/alipay/config.php配置notifyUrl，还要在上面截图的授权回调地址设置notifyUrl</p>
      <h5><span class="badge badge-primary">rsaPrivateKey和alipayPublicKey获取与配置</span></h5>
      <p>(1)、使用支付宝官方提供的生成工具，生成私钥，在线工具地址：https://miniu.alipay.com/keytool/create</p>
      <p><img src="../pay/alipay/step/3.png"/></p>
      <p>(2)、生成后，复制应用私钥到config.php的rsaPrivateKey参数进行配置</p>
      <p>(3)、打开开放平台密钥->接口加签方式->设置，链接：https://open.alipay.com/dev/workspace/key-manage</p>
      <p><img src="../pay/alipay/step/4.png"/></p>
      <p>(4)、把应用公钥配置进去</p>
      <p>(5)、复制应用公钥到config.php的alipayPublicKey参数进行配置</p>
      <p><b>您已完成支付宝当面付的配置！</b></p>
    </div>

  </div>';
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
function setpay(){
  $.ajax({
      type: "POST",
      url: "./pay_select_do.php",
      data: $('#setpay').serialize(),
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
        // 添加失败
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