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
  <h3>活码管理后台 / 活动码</h3>
  <p>管理用户创建的活码数据（查看、停用、删除）</p>
  
  <!-- 左右布局 -->
  <!-- 左侧布局 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">活码管理</button>
    <button type="button" class="btn btn-light"><a href="./">返回首页</a></button>
  </div>';

  //计算总活码数量
  $sql_active = "SELECT * FROM huoma_active";
  $result_active = $conn->query($sql_active);
  $allactive_num = $result_active->num_rows;

  //每页显示的活码数量
  $lenght = 10;

  //当前页码
  @$page = $_GET['p']?$_GET['p']:1;

  //每页第一行
  $offset = ($page-1)*$lenght;

  //总数页
  $allpage = ceil($allactive_num/$lenght);

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

  // 获取落地页域名
  $sql_ym = "SELECT * FROM huoma_yuming";
  $result_ym = $conn->query($sql_ym);

  // 获取群活码列表
  $sql = "SELECT * FROM huoma_active ORDER BY ID DESC limit {$offset},{$lenght}";
  $result = $conn->query($sql);
  
  if ($result->num_rows > 0) {
      echo '<!-- 右侧布局 -->
      <div class="right-nav">
        <table class="table">
          <thead>
            <tr>
              <th>标题</th>
              <th>状态</th>
              <th>创建时间</th>
              <th>结束时间</th>
              <th>用户</th>
              <th>访问</th>
              <th style="text-align: center;">操作</th>
            </tr>
          </thead>
          <tbody>';

          // 遍历数据
          while($row = $result->fetch_assoc()) {
            $active_title = $row["active_title"];
            $active_id = $row["active_id"];
            $active_update_time = $row["active_update_time"];
            $active_pv = $row["active_pv"];
            $active_status = $row["active_status"];
            $active_user = $row["active_user"];
            $active_endtime = $row["active_endtime"];

            // 渲染到UI
            echo '<tr>';
              echo '<td class="td-title">'.$active_title.'</td>';
              if ($active_status == 1) {
                echo '<td class="td-status"><span class="badge badge-success">正常</span></td>';
              }else if ($active_status == 2) {
                echo '<td class="td-status"><span class="badge badge-warning">关闭</span></td>';
              }else if ($active_status == 3) {
                echo '<td class="td-status"><span class="badge badge-danger">停用</span></td>';
              }
              echo '<td class="td-status">'.$active_update_time.'</td>';
              if (empty($active_endtime)) {
                echo '<td class="td-fwl">未设置</td>';
              }else{
                echo '<td class="td-fwl">'.$active_endtime.'</td>';
              }
              echo '<td class="td-fwl">'.$active_user.'</td>
              <td class="td-fwl">'.$active_pv.'</td>
              <td class="td-caozuo" style="text-align: center;">
              <div class="btn-group dropleft">
              <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary" style="cursor:pointer;">•••</span></span>
              <div class="dropdown-menu">
              <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#share_active" id="'.$active_id.'" onclick="shareactive(this);">查看</a>';
              if ($active_status == 3) {
                echo '<a class="dropdown-item" id="'.$active_id.'" onclick="tyactive(this);">启用</a>';
              }else{
                echo '<a class="dropdown-item" id="'.$active_id.'" onclick="tyactive(this);">停用</a>';
              }
              echo '<a class="dropdown-item" href="javascript:;" id="'.$active_id.'" onclick="delactive(this);" title="点击后马上就删除的哦！">删除</a>
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
            echo '<li class="page-item"><a class="page-link" href="./active.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./active.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./active.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./active.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./active.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./active.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./active.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';

  }else{
    echo '<div class="right-nav">暂无活码</div>';
  }

  echo '<!-- 分享模态框 -->
  <div class="modal fade" id="share_active">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">分享活动</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <p class="link"></p>
          <p class="qrcode"></p>
        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
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

// 删除活码
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


// 分享活动
function shareactive(event){
  // 获得当前点击的活码id
  var share_activeid = event.id;
  $.ajax({
      type: "GET",
      url: "./share_active_do.php?activeid="+share_activeid,
      success: function (data) {
        // 分享成功
        $("#share_active .modal-body .link").text("链接："+data.url+"");
        $("#share_active .modal-body .qrcode").html("<img src='../console/qrcode.php?content="+data.url+"' width='200'/>");
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

// 停用活码
function tyactive(event){
  // 获得当前点击的活码id
  var ty_activeid = event.id;
  $.ajax({
      type: "GET",
      url: "./ty_active_do.php?activeid="+ty_activeid,
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

</script>
</body>
</html>