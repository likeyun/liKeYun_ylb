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
  <style>
  #instantclick-bar{
    display: none;
  }
  </style>
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

  // 获取总用户数
  $sql_total_user = "SELECT * FROM huoma_user";
  $result_total_user = $conn->query($sql_total_user);
  $total_user_num = $result_total_user->num_rows;

  // 获取今天新增用户数
  $today_date = date("Y-m-d");
  $sql_today_reg = "SELECT * FROM huoma_user WHERE reg_time LIKE '%$today_date%'";
  $result_today_reg = $conn->query($sql_today_reg);
  $today_reg_num = $result_today_reg->num_rows;

  // 获取今天收款数
  $sql_today_pay = "SELECT pay_money FROM huoma_order WHERE pay_time LIKE '%$today_date%'";
  $result_today_pay = $conn->query($sql_today_pay);
  if ($result_today_pay->num_rows > 0) {
    $pay_nums = 0;
    while($row_today_pay = $result_today_pay->fetch_assoc()) {
      $pay_nums = $pay_nums+$row_today_pay['pay_money'];
    }
  }else{
      $pay_nums = "0";
  }

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title"><a href="./">活码系统管理后台</a></span>
  <span class="admin-login-link"><a href="./account/exit">'.$_SESSION["huoma.dashboard"].' 退出</a></span>
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
  <h3>活码管理后台</h3> 
  <p>便捷管理用户创建的活码数据、用户账号、查看数据</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">数据看板</button>
    <a href="./qun.php?t=home/qun&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">微信群活码</button></a>
    <a href="./wx.php?t=home/wx&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">微信号活码</button></a>
    <a href="./active.php?t=home/active&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">活动码</button></a>
    <a href="./user.php?t=home/user&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">用户管理</button></a>
    <a href="./order.php?t=home/order&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">订单管理</button></a>
    <a href="./taocan.php?t=home/taocan&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">续费套餐</button></a>
    <a href="./yqm.php?t=home/yqm&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">邀请码</button></a>
    <a href="./addons.php?t=home/addons&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">插件中心</button></a>
    <a href="./set.php?t=home/set&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">系统设置</button></a>
  </div>';

  echo '<!-- 右侧布局 -->
  <div class="right-nav">
    <div class="jumbotron" style="padding:30px 20px 10px 20px;">
      <h2>欢迎使用liKeYun活码系统</h2> 
      <p>这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。</p> 
    </div>
    <!-- 数据看板 -->
    <div class="data-board">
      <div class="alert alert-success">
        <div class="title"><h5>总用户</h5></div>
        <div class="num"><h3>'.$total_user_num.'</h3></div>
      </div>
      <div class="alert alert-primary">
        <div class="title"><h5>今日新增</h5></div>
        <div class="num"><h3>'.$today_reg_num.'</h3></div>
      </div>
      <div class="alert alert-warning">
        <div class="title"><h5>今日收款</h5></div>
        <div class="num"><h3>¥'.$pay_nums.'</h3></div>
      </div>
    </div>
    <p style="color:#999;font-size:13px;line-height:5px;margin-top:20px;"><a href="http://www.likeyuns.com" style="text-decoration:none;color:#999;" target="blank">作者资源网站： www.likeyuns.com</a></p>
    <p style="color:#999;font-size:13px;line-height:5px;"><a href="https://segmentfault.com/u/tanking" style="text-decoration:none;color:#999;" target="blank">作者技术博客：https://segmentfault.com/u/tanking</a></p>
    <p style="color:#999;font-size:13px;line-height:5px;"><a href="https://github.com/likeyun/liKeYun_Huoma" style="text-decoration:none;color:#999;" target="blank">Github开源地址：https://github.com/likeyun/liKeYun_Huoma</a></p>
    <p style="color:#999;font-size:13px;line-height:5px;"><a href="http://imgupload.likeyuns.com/" style="text-decoration:none;color:#999;" target="blank">作者在线图床：http://imgupload.likeyuns.com</a></p>
    <p style="color:#999;font-size:13px;line-height:5px;"><a href="../install/qun_qrcode.png" style="text-decoration:none;color:#999;" target="blank">加入开发者交流群：点击这里，微信扫码进群</a></p>
  </div>';
}else{
  // 跳转到登陆界面
  header("Location:../dashboard/account/login/");
}
?>

</div>

<script>
// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}

//监听个人微信二维码的显示状态
$("#grwx_status").bind('input propertychange',function(e){
  //获取当前点击的状态
  var grwx_status = $(this).val();
  //如果开启备用群，则需要显示上传二维码和设置最大值
  if (grwx_status == 1) {
    $("#grwx_upload").css("display","block");
  }else if (grwx_status == 2) {
    //否则隐藏，不显示
    $("#grwx_upload").css("display","none");
  }
})

// 创建群活码
function addqun(){
  $.ajax({
      type: "POST",
      url: "./add_qun_do.php",
      data: $('#addqun').serialize(),
      success: function (data) {
        // 创建成功
        if (data.code==100) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#add_qun_hm').modal('hide');
          // 刷新列表
          location.reload();
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 创建失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 删除群活码
function delqun(event){
  // 获得当前点击的群活码id
  var del_qun_hmid = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_qun_do.php?hmid="+del_qun_hmid,
      success: function (data) {
        if (data.code == "100") {
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
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 分享群活码
function sharequn(event){
  // 获得当前点击的群活码id
  var share_qun_hmid = event.id;
  $.ajax({
      type: "GET",
      url: "./share_qun_do.php?hmid="+share_qun_hmid,
      success: function (data) {
        // 分享成功
        $("#share_qun .modal-body .link").text("链接："+data.url+"");
        $("#share_qun .modal-body .qrcode").html("<img src='./qrcode.php?content="+data.url+"' width='200'/>");
      },
      error : function() {
        // 分享失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 上传微信群二维码
var wxqrcode_lunxun = setInterval("upload_wxqrcode()",2000);
  function upload_wxqrcode() {
  var wxqrcode_filename = $("#select_wxqrcode").val();
  if (wxqrcode_filename) {
    clearInterval(wxqrcode_lunxun);
    var addqun_form = new FormData(document.getElementById("addqun"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:addqun_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#grwx_upload .form-control").val(data.path);
          $("#grwx_upload .text").text("已上传");
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data+"</strong></div>");
        }
      },
      error:function(data){
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
      }
    })
    // 关闭信息提示框
    setTimeout('closesctips()', 2000);
  }else{
    // console.log("等待上传");
  }
}
</script>
</body>
</html>
