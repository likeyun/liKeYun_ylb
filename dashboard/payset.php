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
  <script type="text/javascript">
    // 初始化支付接口
    function cshpay(){
      $.ajax({
          type: "POST",
          url: "./csh_payapi.php",
          success: function (data) {
            // 成功
            if (data.code==100) {
              alert(data.msg);
              // 刷新列表
              location.reload();
            }else{
              alert(data.msg);
            }
          },
          error : function() {
            // 失败
            alert("服务器发生错误");
          }
      });
    }
  </script>
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
  <h3>活码管理后台 / 系统设置 / 支付接口</h3> 
  <p>配置支付接口，设置支付API</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">支付选项</button>
    <button type="button" class="btn btn-light"><a href="./alipay_set.php">支付宝当面付</a></button>
    <button type="button" class="btn btn-light"><a href="./xdd_set.php">小叮当支付</a></button>
    <button type="button" class="btn btn-light"><a href="./payjs_set.php">PayJs</a></button>
    <button type="button" class="btn btn-light"><a href="./easypay_set.php">易支付</a></button>
    <button type="button" class="btn btn-light"><a href="./set.php">返回上一页</a></button>
    <button type="button" class="btn btn-light"><a href="./index.php">返回首页</a></button>
  </div>';


  echo '<!-- 右侧布局 -->
  <form onsubmit="return false" id="setpay">
  <div class="right-nav">';
  echo '<div class="alert alert-primary">下方选择的支付接口，将用于线上交易支付，例如注册，续费。如果需要加入自己已有的支付接口，可以联系作者付费定制，付费标准是一个接口200元起，具体收费以沟通为准。作者联系方式请在作者博客找到：<a href="https://segmentfault.com/u/tanking" target="blank">https://segmentfault.com/u/tanking</a></div>';
  
  // 获取微信支付API选项
  $sql_wxpay = "SELECT * FROM huoma_payselect WHERE paytype='wx' ORDER BY ID ASC";
  $result_wxpay = $conn->query($sql_wxpay);

  $sql_wxpay_select = "SELECT * FROM huoma_payselect WHERE paytype='wx' AND payselect='2'";
  $result_wxpay_select = $conn->query($sql_wxpay_select);

  // 获取支付宝API选项
  $sql_alipay = "SELECT * FROM huoma_payselect WHERE paytype='ali' ORDER BY ID ASC";
  $result_alipay = $conn->query($sql_alipay);

  $sql_alipay_select = "SELECT * FROM huoma_payselect WHERE paytype='ali' AND payselect='2'";
  $result_alipay_select = $conn->query($sql_alipay_select);
  
  if ($result_wxpay->num_rows > 0 && $result_wxpay_select->num_rows > 0) {

          echo '<select class="form-control" name="wxpay" style="-webkit-appearance:none;margin-bottom:20px;">';
          // 获取已经选择的选项
          while($row_wxpay_select = $result_wxpay_select->fetch_assoc()) {
            // 遍历数据
            echo '<option value="'.$row_wxpay_select["id"].'">'.$row_wxpay_select["paytitle"].'（点击切换）</option>';
          }

          while($row_wxpay = $result_wxpay->fetch_assoc()) {
            $id = $row_wxpay["id"];
            $paytitle = $row_wxpay["paytitle"];
            // 渲染到UI
            echo '<option value="'.$id.'">'.$paytitle.'</option>';
          }
          echo '</select>';

  }else{
    echo '<button type="button" class="btn btn-dark" onclick="cshpay();">首次使用，请点击这里初始化支付配置</button>';
    exit;
  }
  if ($result_alipay->num_rows > 0) {

          echo '<select class="form-control" name="alipay" style="-webkit-appearance:none;margin-bottom:20px;">';
          // 获取已经选择的选项
          while($row_alipay_select = $result_alipay_select->fetch_assoc()) {
            // 遍历数据
            echo '<option value="'.$row_alipay_select["id"].'">'.$row_alipay_select["paytitle"].'（点击切换）</option>';
          }

          while($row_alipay = $result_alipay->fetch_assoc()) {
            $id = $row_alipay["id"];
            $paytitle = $row_alipay["paytitle"];
            // 渲染到UI
            echo '<option value="'.$id.'">'.$paytitle.'</option>';
          }
          echo '</select>';

  }else{
    echo '系统发生错误';
  }

  echo '<button type="button" class="btn btn-dark" onclick="setpay();">提交设置</button>';
  echo '</div>
  </form>';
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
