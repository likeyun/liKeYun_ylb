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
  <h3>活码管理后台 / 邀请码</h3>
  <p>邀请码管理（查看、创建、停用、删除）</p>
  
  <!-- 左右布局 -->
  <!-- 左侧布局 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">邀请码列表</button>
    <button type="button" class="btn btn-light" data-toggle="modal" data-target="#creat_yqm">导入邀请码</button>
    <button type="button" class="btn btn-light"><a href="./">返回首页</a></button>
  </div>';

  //计算总活码数量
  $sql_yqm = "SELECT * FROM huoma_yqm";
  $result_yqm = $conn->query($sql_yqm);
  $allyqm_num = $result_yqm->num_rows;

  //每页显示的活码数量
  $lenght = 10;

  //当前页码
  @$page = $_GET['p']?$_GET['p']:1;

  //每页第一行
  $offset = ($page-1)*$lenght;

  //总数页
  $allpage = ceil($allyqm_num/$lenght);

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

  // 获取群活码列表
  $sql = "SELECT * FROM huoma_yqm ORDER BY ID DESC limit {$offset},{$lenght}";
  $result = $conn->query($sql);
  
  if ($result->num_rows > 0) {
      echo '<!-- 右侧布局 -->
      <div class="right-nav">
        <table class="table">
          <thead>
            <tr>
              <th>邀请码</th>
              <th>状态</th>
              <th>使用时间</th>
              <th>天数</th>
              <th style="text-align: center;">操作</th>
            </tr>
          </thead>
          <tbody>';

          // 遍历数据
          while($row = $result->fetch_assoc()) {
            $yqm = $row["yqm"];
            $yqm_usetime = $row["yqm_usetime"];
            $yqm_daynum = $row["yqm_daynum"];
            $yqm_status = $row["yqm_status"];

            // 渲染到UI
            echo '<tr>';
              echo '<td class="td-title">'.$yqm.'</td>';
              if ($yqm_status == 1) {
                echo '<td class="td-status"><span class="badge badge-success">未用</span></td>';
              }else{
                echo '<td class="td-status"><span class="badge badge-danger">已用</span></td>';
              }
              echo '<td class="td-status">'.$yqm_usetime.'</td>
              <td class="td-fwl">'.$yqm_daynum.'</td>
              <td class="td-caozuo" style="text-align: center;">
              <div class="btn-group dropleft">
              <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary" style="cursor:pointer;">•••</span></span>
              <div class="dropdown-menu">';
              if ($yqm_status == 2) {
                echo '<a class="dropdown-item" href="javascript:;" id="'.$yqm.'" onclick="tyyqm(this);">恢复</a>';
              }else if ($yqm_status == 1) {
                echo '<a class="dropdown-item" href="javascript:;" id="'.$yqm.'" onclick="tyyqm(this);">停用</a>';
              }
                echo '<a class="dropdown-item" href="javascript:;" id="'.$yqm.'" onclick="delyqm(this);" title="点击后马上就删除的哦！">删除</a>
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
            echo '<li class="page-item"><a class="page-link" href="./yqm.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./yqm.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./yqm.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./yqm.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./yqm.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./yqm.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./yqm.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';

  }else{
    echo '<div class="right-nav">暂无邀请码</div>';
  }

echo '</div>';
}else{
  // 跳转到登陆界面
  header("Location:../LoginReg/Login.html");
}
?>

<!-- 创建邀请码 -->
<div class="modal fade" id="creat_yqm">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
 
      <!-- 模态框头部 -->
      <div class="modal-header">
        <h4 class="modal-title">导入邀请码</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form onsubmit="return false" id="daoru_yqm" enctype="multipart/form-data">
      <!-- 模态框主体 -->
      <div class="modal-body">
        <!-- txt导入 -->
        <div class="upload_txt">
          上传txt文件
          <input type="file" class="upload_txt_file" id="select_txt" name="file">
        </div>
        <!-- 上传格式提示 -->
        <p>* 每行格式：邀请码|可用天数</p>
        <p>* 例如：rthskjyu|3</p>
        <p>* 一行一个，建议每次最多上传100行，太多上传容易出错。</p>
        <p>* 示例文件：<a href="./yqm.txt" download="yqm">点击下载</a></p>
      </div>
      </form>
    </div>
  </div>
</div>

<script>

// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}

function reload_yqmpage(){
  location.reload();
}

// 更新邀请码状态
function tyyqm(event){
  // 获得当前点击的邀请码
  var this_yqm = event.id;
  $.ajax({
      type: "GET",
      url: "./ty_yqm_do.php?yqm="+this_yqm,
      success: function (data) {
        // 处理成功
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
        location.reload();
      },
      error : function() {
        // 处理失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}

// 删除邀请码
function delyqm(event){
  // 获得当前点击的邀请码
  var del_yqm = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_yqm_do.php?yqm="+del_yqm,
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

// 上传txt
var txt_lunxun = setInterval("upload_txt()",2000);
  function upload_txt() {
  var txt_filename = $("#select_txt").val();
  if (txt_filename) {
    clearInterval(txt_lunxun);
    var daoru_txt_form = new FormData(document.getElementById("daoru_yqm"));
    $.ajax({
      url:"./daoru_yqm.php",
      type:"post",
      data:daoru_txt_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.code == 200) {
          $("#creat_yqm .modal-body").html("<h2 style='text-align:center;'>"+data.msg+"</h2>");
          setTimeout('reload_yqmpage()', 2000);
        }else{
          $("#creat_yqm .modal-body").html("<h2 style='text-align:center;'>"+data.msg+"</h2>");
        }
      },
      error:function(){
        $("#creat_yqm .modal-body").html("<h2>服务器发生错误</h2>");
      },
      beforeSend:function(){
        $("#creat_yqm .modal-body").html("<h3 style='text-align:center;'>正在导入...</h3>");
      }
    })
  }else{
    // console.log("等待上传");
  }
}
</script>
</body>
</html>