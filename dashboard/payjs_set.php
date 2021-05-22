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
  <h3>活码管理后台 / 系统设置 / 支付接口 / PayJs支付</h3> 
  <p>配置支付接口，设置支付API</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-light"><a href="./payset.php">支付选项</a></button>
    <button type="button" class="btn btn-light"><a href="./alipay_set.php">支付宝当面付</a></button>
    <button type="button" class="btn btn-light"><a href="./xdd_set.php">小叮当支付</a></button>
    <button type="button" class="btn btn-dark">PayJs</button>
    <button type="button" class="btn btn-light"><a href="./set.php">返回上一页</a></button>
    <button type="button" class="btn btn-light"><a href="./index.php">返回首页</a></button>
  </div>';


  echo '<!-- 右侧布局 -->
  <div class="right-nav">';
  echo '<div class="jumbotron">
    <h4>PayJs支付配置教程（文档更新时间：2021-04-30）</h4>
    <br/>
    <p>1、请打开本程序根目录下的 pay/payjs/config.php</p>
    <p>2、把你自己的PayJs支付的mchid（商户号）、key（通信密钥）都填写进去即可</p>
    <p><img src="../pay/payjs/step/step1.png" /></p>
    <h5><span class="badge badge-success">什么是PayJs</span></h5>
    <p>PayJs是一个提供微信官方、支付宝官方支付接口的第三方服务商，通过PayJs间接与微信、支付宝签约，获得官方接口权限。PayJs仅仅是作为一个中间商，帮助开发者进行调用官方接口。我们作为个人开发者，是没有资质向支付宝、微信官方申请企业支付接口的，但PayJs是北京顶风科技有限公司推出的互联网产品，PayJs是有资质的，那么PayJs可以成为支付宝、微信的官方服务代理商，帮我们代理支付接口，完成支付流程。即PayJs的微信和支付宝是官方接口，需要遵循官方的规定。</p>
    
    <p>PayJs申请：<a href="https://payjs.cn/ref/dnxbjd" target="blank">https://payjs.cn/ref/dnxbjd</a></p>
  </div>';
  echo '</div>';
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