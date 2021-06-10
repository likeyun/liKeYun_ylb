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
  <h3>活码管理后台 / 系统设置 / 支付接口 / 小叮当支付</h3> 
  <p>配置支付接口，设置支付API</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-light"><a href="./payset.php">支付选项</a></button>
    <button type="button" class="btn btn-light"><a href="./alipay_set.php">支付宝当面付</a></button>
    <button type="button" class="btn btn-dark">小叮当支付</button>
    <button type="button" class="btn btn-light"><a href="./payjs_set.php">PayJs</a></button>
    <button type="button" class="btn btn-light"><a href="./easypay_set.php">易支付</a></button>
    <button type="button" class="btn btn-light"><a href="./set.php">返回上一页</a></button>
    <button type="button" class="btn btn-light"><a href="./index.php">返回首页</a></button>
  </div>';

echo '<!-- 右侧布局 -->
  <div class="right-nav">
    
    <div class="jumbotron">
      <h4>小叮当支付配置教程（文档更新时间：2021-04-30）</h4>
      <br/>
      <p>1、请打开本程序根目录下的 pay/xdd/config.php</p>
      <p>2、把你自己的小叮当支付的app_key、app_id都填写进去即可</p>
      <h5><span class="badge badge-warning">app_key和app_id获取方法</span></h5>
      <p>(1)、首先打开小叮当网站：http://www.xddpay.com/ 注册后登陆</p>
      <p>(2)、点击[管理中心]->API接口->详细</p>
      <p><img src="../pay/xdd/step/1.png" /></p>
      <p>同时我们也要设置好平台名称、接入网站域名、异步通知网址，并勾选启用和保存，同步通知网址不需要设置。</p>
      <h5><span class="badge badge-warning">异步通知网址获取方法</span></h5>
      <p>异步通知网址是 pay/xdd/notify.php在线上服务器对应的URL，例如你的域名是http://www.baidu.com，你的活码系统部署在根目录下的huoma目录，那么异步通知网址应该填写的链接是：http://www.baidu.com/huoma/pay/xdd/notify.php</p>
      <h5><span class="badge badge-warning">充值</span></h5>
      <p>因为小叮当支付是需要收取一定的接口调用费的，所以需要点击余额明细进去充值，先充几块钱进去。</p>
      <h5><span class="badge badge-warning">上传二维码</span></h5>
      <p>因为小叮当支付是免签支付，微信支付是需要上传二维码的。这个是根据你活码系统设置的套餐进行设置，例如你设置了3个套餐，套餐一是9.90元开一个月，为了防止发起多次支付或者多人同时发起支付带来的金额相同，导致无法辨别不同用户的支付，所以我们需要设置每个收款码为0.01元的差值。</p>
      <p><b>在上传收款码之前，我们先了解下免签约支付的工作原理</b></p>
      <p>小叮当支付就是免签约支付，工作原理就是通过手机端APP监听微信支付收款，当用户扫描你的收款码支付后，你的手机安装的小叮当支付APP会立马监听到这笔收款，然后将这笔收款的信息通过小叮当APP的服务器通知给活码系统的服务器，活码系统收到通知，就在扫码支付续费页面告诉用户支付成功，完成续费。</p>
      <p>但是，小叮当APP无法识别是哪个用户支付的，无法获取订单号，也无法获取微信用户的昵称等信息，仅仅是获得这笔收款的金额，即同时有多个人进行续费，你是无法区别是谁给你付了这笔钱的，这样就很麻烦，大家同时扫码支付，收到的金额都是9.90元的话，那么通知哪一个页面完成续费呢？所以这就需要我们上传不同金额的收款码。</p>
      <p>每个收款码之间，用0.01元作为差距，例如用户续费的套餐是9.90元，那么A用户扫码的是显示9.91元的收款码，B用户扫码的是显示9.92元的收款码，C用户扫码的是显示9.93元的收款码，以此类推，你可以设置10个收款码，每个收款码的差值是0.01元，这样小叮当APP收到通知，就能知道究竟是谁支付了。</p>
      <p><b>我们接着上传二维码</b></p>
      <p>点击收款二维码，添加定价，添加10个差值为0.01的定价，根据你的套餐进行添加，例如你的套餐一是9.90元，那么10个定价分别是9.90、9.91、9.92、9.93、9.94、9.95、9.96、9.97、9.98、9.99，然后去微信收款，生成10张固定金额的收款码，上传就行了。</p>
      <p><img src="../pay/xdd/step/2.png" /></p>
      <p>支付宝的有技术实现自动生成二维码，不需要上传二维码，只需要根据小叮当后台的文档配置就行。</b></p>
      <h5><span class="badge badge-warning">安装APP</span></h5>
      <p>因为小叮当是通过APP监听收款信息的，所以需要安装他们的APP，安装之后，登陆账号，简单设置一下就可以。<a href="http://www.xddpay.com/doc/download.htm">APP下载和设置教程</a></p>
      <p><b>您已完成小叮当支付的配置！</b></p>
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