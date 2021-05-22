<!DOCTYPE html>
<html>
<head>
  <title>里客云开源活码系统</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdn.staticfile.org/popper.js/1.15.0/umd/popper.min.js"></script>
  <script src="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="../js/wangEditor.min.js"></script>
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

  // 创建连接
  $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title">里客云开源活码系统</span>
  <span class="admin-login-link"><a href="./account/exit">'.$_SESSION["huoma.dashboard"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">
  <br/>
  <h3>活码管理后台 / 订单管理</h3>
  <p>查看用户注册、续费的订单</p>
  
  <!-- 左右布局 -->
  <!-- 左侧布局 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">订单列表</button>
    <button type="button" class="btn btn-light" data-toggle="modal" data-target="#search_order">搜索订单</button>
    <button type="button" class="btn btn-light"><a href="./">返回首页</a></button>
  </div>';

  if (trim(empty($_GET["order_no"]))) {
    $sql_order = "SELECT * FROM huoma_order";
  }else{
    $sql_order = "SELECT * FROM huoma_order WHERE order_no = '$_GET[order_no]'";
  }

  //计算总活码数量
  $sql_order = "SELECT * FROM huoma_order";
  $result_order = $conn->query($sql_order);
  $allorder_num = $result_order->num_rows;

  //每页显示的订单数量
  $lenght = 10;

  //当前页码
  @$page = $_GET['p']?$_GET['p']:1;

  //每页第一行
  $offset = ($page-1)*$lenght;

  //总数页
  $allpage = ceil($allorder_num/$lenght);

  //上一页     
  $prepage = $page-1;
  if($page==1){
    $prepage=1;
  }

  //下一页
  $nextpage = $page+1;
  if($page==$allpage){
    $nextpage=$allpage;
  }

  // 获取订单列表
  if (trim(empty($_GET["order_no"]))) {
    $sql = "SELECT * FROM huoma_order ORDER BY ID DESC limit {$offset},{$lenght}";
  }else{
    $sql = "SELECT * FROM huoma_order WHERE order_no = '$_GET[order_no]' ORDER BY ID DESC limit {$offset},{$lenght}";
  }

  $result = $conn->query($sql);
  
  if ($result->num_rows > 0) {
      echo '<!-- 右侧布局 -->
      <div class="right-nav">
        <table class="table">
          <thead>
            <tr>
              <th>订单号</th>
              <th>用户ID</th>
              <th>时间</th>
              <th>金额</th>
              <th>套餐</th>
              <th>支付渠道</th>
              <th style="text-align: center;">操作</th>
            </tr>
          </thead>
          <tbody>';

          // 遍历数据
          while($row = $result->fetch_assoc()) {
            $order_no = $row["order_no"];
            $user_id = $row["user_id"];
            $pay_time = $row["pay_time"];
            $pay_money = $row["pay_money"];
            $xufei_daynum = $row["xufei_daynum"];
            $pay_type = $row["pay_type"];

            // 渲染到UI
            echo '<tr>';
              echo '<td class="td-title">'.$order_no.'</td>';
              echo '<td class="td-status">'.$user_id.'</td>';
              echo '<td class="td-status">'.$pay_time.'</td>
              <td class="td-fwl">'.$pay_money.'元</td>
              <td class="td-fwl">'.$xufei_daynum.'天</td>
              <td class="td-fwl">'.$pay_type.'</td>
              <td class="td-caozuo" style="text-align: center;"><span class="badge badge-secondary" style="cursor:pointer;" title="点击后立马删除" id="'.$order_no.'" onclick="delorder(this);">删除</span></td>';
            echo '</tr>';
          }

          // 分页
          echo '<div class="fenye"><ul class="pagination pagination-sm">';
          if ($page == 1 && $allpage == 1) {
            // 当前页面是第一页，并且仅有1页
            // 不显示翻页控件
          }else if ($page == 1) {
            // 当前页面是第一页，还有下一页
            echo '<li class="page-item"><a class="page-link" href="./order.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./order.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./order.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./order.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./order.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./order.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./order.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';

  }else{
    echo '<div class="right-nav">暂无订单</div>';
  }
echo '</div>';
}else{
  // 跳转到登陆界面
  header("Location:../LoginReg/Login.html");
}
?>

<!-- 搜索订单 -->
<div class="modal fade" id="search_order">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
 
      <!-- 模态框头部 -->
      <div class="modal-header">
        <h4 class="modal-title">搜索订单</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
 
      <!-- 模态框主体 -->
      <div class="modal-body">
        <!-- 邮箱 -->
        <form method="get" action="./order.php">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">订单号</span>
          </div>
          <input type="text" class="form-control" placeholder="请输入订单号" name="order_no">
        </div>

        <!-- 提交 -->
        <input type="submit" class="btn btn-dark" value="搜索订单"/>
        </form>
      </div>
 
    </div>
  </div>
</div>

<script>
// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}

// 删除群活码
function delorder(event){
  // 获得当前点击的订单号
  var del_order_no = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_order_do.php?order_no="+del_order_no,
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

</script>
</body>
</html>