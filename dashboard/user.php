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
  <h3>活码管理后台 / 用户管理</h3>
  <p>管理用户账号（查看、编辑、停用、删除）</p>
  
  <!-- 左右布局 -->
  <!-- 左侧布局 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">用户列表</button>
    <button type="button" class="btn btn-light" data-toggle="modal" data-target="#search_user">搜索用户</button>
    <button type="button" class="btn btn-light"><a href="./">返回首页</a></button>
  </div>';

  if (trim(empty($_GET["user"]))) {
     $sql_user = "SELECT * FROM huoma_user";
  }else{
    $sql_user = "SELECT * FROM huoma_user WHERE user = '$_GET[user]'";
  }

  //计算总用户数量
  $result_user = $conn->query($sql_user);
  $alluser_num = $result_user->num_rows;

  //每页显示的活码数量
  $lenght = 10;

  //当前页码
  @$page = $_GET['p']?$_GET['p']:1;

  //每页第一行
  $offset = ($page-1)*$lenght;

  //总数页
  $allpage = ceil($alluser_num/$lenght);

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

  // 获取用户列表
  if (trim(empty($_GET["user"]))) {
    $sql = "SELECT * FROM huoma_user ORDER BY ID DESC limit {$offset},{$lenght}";
  }else{
    $sql = "SELECT * FROM huoma_user WHERE user = '$_GET[user]' ORDER BY ID DESC limit {$offset},{$lenght}";
  }

  $result = $conn->query($sql);
  
  if ($result->num_rows > 0) {
      echo '<!-- 右侧布局 -->
      <div class="right-nav">
        <table class="table">
          <thead>
            <tr>
              <th>账号</th>
              <th>状态</th>
              <th>注册</th>
              <th>到期</th>
              <th>邮箱</th>
              <th style="text-align: center;">操作</th>
            </tr>
          </thead>
          <tbody>';

          // 遍历数据
          while($row = $result->fetch_assoc()) {
            $user = $row["user"];
            $reg_time = $row["reg_time"];
            $expire_time = $row["expire_time"];
            $user_limit = $row["user_limit"];
            $user_status = $row["user_status"];
            $email = $row["email"];
            $user_id = $row["user_id"];
            $pwd = $row["pwd"];

            // 判断到期时间
            if ($expire_time <= date("Y-m-d")) {
              $user_status = 3;
            }

            // 渲染到UI
            echo '<tr>';
              echo '<td class="td-title" style="width:170px;">'.$user.'</td>';
              if ($user_status == 1) {
                echo '<td class="td-status"><span class="badge badge-success">正常</span></td>';
              }else if($user_status == 2) {
                echo '<td class="td-status"><span class="badge badge-danger">停用</span></td>';
              }else if($user_status == 3) {
                echo '<td class="td-status"><span class="badge badge-warning">到期</span></td>';
              }
              echo '<td class="td-status">'.$reg_time.'</td>
              <td class="td-fwl">'.$expire_time.'</td>
              <td class="td-fwl">'.$email.'</td>
              <td class="td-caozuo" style="text-align: center;">
              <div class="btn-group dropleft">
              <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary" style="cursor:pointer;">•••</span></span>
              <div class="dropdown-menu">
              <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#edi_user" id="'.$user_id.'" onclick="getuserinfo(this);">编辑</a>';
              if ($user_status == 2) {
                echo '<a class="dropdown-item" href="javascript:;" id="'.$user_id.'" onclick="tyuser(this);">恢复</a>';
              }else if ($user_status == 1) {
                echo '<a class="dropdown-item" href="javascript:;" id="'.$user_id.'" onclick="tyuser(this);">停用</a>';
              }
              echo '<a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#xufei" id="'.$user_id.'" onclick="getxufei_userid(this);">续期</a>
              <a class="dropdown-item" href="javascript:;" id="'.$user_id.'" onclick="delactive(this);" title="点击后马上就删除的哦！">删除</a>
              </div>
              </div>
              </td>';
            echo '</tr>';
          }

          // 分页
          echo '<div class="fenye"><ul class="pagination pagination-sm">';
          if ($page == 1 && $allpage == 1) {
            // 当前页面是第一页，并且仅有1页
            // 不显示翻页控件
          }else if ($page == 1) {
            // 当前页面是第一页，还有下一页
            echo '<li class="page-item"><a class="page-link" href="./user.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./user.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./user.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./user.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./user.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./user.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./user.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';

  }else{
    echo '<div class="right-nav">暂无用户</div>';
  }

  echo '<!-- 编辑用户模态框 -->
  <div class="modal fade" id="edi_user">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">编辑用户信息</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <!-- 邮箱 -->
          <form onsubmit="return false" id="ediuser" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">账号</span>
            </div>
            <input type="email" class="form-control" disabled="disabled" placeholder="请输入账号" name="user" id="u_name">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">邮箱</span>
            </div>
            <input type="email" class="form-control" placeholder="请输入邮箱" name="email" id="u_email">
          </div>

          <input type="hidden" name="user_id" id="u_id"/>

          <!-- 提交 -->
          <button type="button" class="btn btn-dark" onclick="ediuser();">更新信息</button>
          </form>
        </div>
   
      </div>
    </div>
  </div>

  <!-- 续期 -->
  <div class="modal fade" id="xufei">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">续期</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <!-- 邮箱 -->
          <form onsubmit="return false" id="xufei_form">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">续期天数</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入续期天数" name="xufei_days">
          </div>

          <input type="hidden" name="user_id" id="xufei_userid"/>

          <!-- 提交 -->
          <button type="button" class="btn btn-dark" onclick="xufei();">续期</button>
          </form>
        </div>
   
      </div>
    </div>
  </div>

  <!-- 搜索用户 -->
  <div class="modal fade" id="search_user">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">搜索用户</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <!-- 邮箱 -->
          <form method="get" action="./user.php">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">用户账号</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入用户账号" name="user">
          </div>

          <!-- 提交 -->
          <input type="submit" class="btn btn-dark" value="搜索用户"/>
          </form>
        </div>
   
      </div>
    </div>
  </div>

</div>';
}else{
  // 跳转到登陆界面
  header("Location:../LoginReg/Login.html");
}
?>

<script>
// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}


// 获得账号信息
function getuserinfo(event){
  var get_user_id = event.id;
  $.ajax({
      type: "GET",
      url: "./get_user_info.php?userid="+get_user_id,
      data: $('#ediuser').serialize(),
      success: function (data) {
        // 获取成功
        if (data.code==100) {
          $("#u_email").val(data.email);
          $("#u_id").val(get_user_id);
          $("#u_name").val(data.user);
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div cout_trade_nolass=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 获取失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 更新账号信息
function ediuser(){
  $.ajax({
      type: "POST",
      url: "./update_user_do.php",
      data: $('#ediuser').serialize(),
      success: function (data) {
        // 更新成功
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
        // 更新失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 删除用户
function delactive(event){
  // 获得当前点击的活码id
  var del_activeid = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_active_do.php?activeid="+del_activeid,
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


// 停用活码
function tyuser(event){
  // 获得当前点击的活码id
  var ty_userid = event.id;
  $.ajax({
      type: "GET",
      url: "./ty_user_do.php?userid="+ty_userid,
      success: function (data) {
        // 停用成功
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
        location.reload();
      },
      error : function() {
        // 停用失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 获得需要续费的用户id
function getxufei_userid(event){
  var xufei_userid = event.id;
  $("#xufei_userid").val(xufei_userid);
}

// 续费
function xufei(){
  $.ajax({
      type: "POST",
      url: "./xufei_do.php",
      data: $('#xufei_form').serialize(),
      success: function (data) {
        // 更新成功
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
        // 更新失败
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