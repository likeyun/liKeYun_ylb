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
  <span class="admin-title"><a href="./">里客云开源活码系统</a></span>
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
  <h3>活码管理后台 / 微信群活码</h3> 
  <p>管理用户创建的活码数据（查看、停用、删除）</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">活码管理</button>
    <button type="button" class="btn btn-light"><a href="./index.php">返回首页</a></button>
  </div>';

      //计算总活码数量
      $sql_huoma = "SELECT * FROM huoma_qun";
      $result_huoma = $conn->query($sql_huoma);
      $allhuoma_num = $result_huoma->num_rows;

      //每页显示的活码数量
      $lenght = 10;

      //当前页码
      @$page = $_GET['p']?$_GET['p']:1;

      //每页第一行
      $offset = ($page-1)*$lenght;

      //总数页
      $allpage = ceil($allhuoma_num/$lenght);

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
      $sql = "SELECT * FROM huoma_qun ORDER BY ID DESC limit {$offset},{$lenght}";
      $result = $conn->query($sql);
       
      if ($result->num_rows > 0) {

          echo '<!-- 右侧布局 -->
          <div class="right-nav">
          <table class="table">
          <thead>
          <tr>
          <th>标题</th>
          <th>状态</th>
          <th>时间</th>
          <th>用户</th>
          <th>访问</th>
          <th style="text-align: center;">操作</th>
          </tr>
          </thead>
          <tbody>';

          // 输出数据
          while($row = $result->fetch_assoc()) {
            // 遍历数据
            $qun_hmid = $row["qun_hmid"];
            $qun_title = $row["qun_title"];
            $qun_status = $row["qun_status"];
            $qun_creat_time = $row["qun_creat_time"];
            $qun_pv = $row["qun_pv"];
            $qun_user = $row["qun_user"];

            // 加载到ui模板
            echo '<tr>
            <td class="td-title">'.$qun_title.'</td>';
            if ($qun_status == 1) {
              echo '<td class="td-status"><span class="badge badge-success">正常</span></td>';
            }else if ($qun_status == 2) {
              echo '<td class="td-status"><span class="badge badge-warning">暂停</span></td>';
            }else if ($qun_status == 3) {
              echo '<td class="td-status"><span class="badge badge-danger">停用</span></td>';
            }
            echo '<td class="td-status">'.$qun_creat_time.'</td>
            <td class="td-fwl">'.$qun_user.'</td>
            <td class="td-fwl">'.$qun_pv.'</td>
            <td class="td-caozuo" style="text-align: center;">
            <div class="btn-group dropleft" style="cursor:pointer;">
            <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary">•••</span></span>
            <div class="dropdown-menu">
            <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#share_qun" id="'.$qun_hmid.'" onclick="sharequn(this);">查看</a>';
            if ($qun_status == 3) {
              echo '<a class="dropdown-item" id="'.$qun_hmid.'" onclick="tyqun(this);">启用</a>';
            }else{
              echo '<a class="dropdown-item" id="'.$qun_hmid.'" onclick="tyqun(this);">停用</a>';
            }
            echo '<a class="dropdown-item" href="javascript:;" id="'.$qun_hmid.'" onclick="delqun(this);" title="点击后马上就删除的哦！">删除</a>
            </div>
            </div>
            </td>
            </tr>';
          }

          // 分页
          echo '<div class="fenye"><ul class="pagination pagination-sm">';
          if ($page == 1 && $allpage == 1) {
            // 当前页面是第一页，并且仅有1页
            // 不显示翻页控件
          }else if ($page == 1) {
            // 当前页面是第一页，还有下一页
            echo '<li class="page-item"><a class="page-link" href="./qun.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./qun.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./">首页</a></li>
            <li class="page-item"><a class="page-link" href="./qun.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./">首页</a></li>
            <li class="page-item"><a class="page-link" href="./qun.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./qun.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';
      } else {
        echo '<div class="right-nav">暂无活码</div>';
      }
      // 断开数据库连接
      $conn->close();

      
}else{
  // 跳转到登陆界面
  header("Location:../account/login/");
}
?>

  <!-- 分享群活码 -->
  <div class="modal fade" id="share_qun">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">分享群活码</h4>
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

</div>

<script>
// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}

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
        $("#share_qun .modal-body .qrcode").html("<img src='../console/qrcode.php?content="+data.url+"' width='200'/>");
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

// 停用群活码
function tyqun(event){
  // 获得当前点击的群活码id
  var ty_qun_hmid = event.id;
  $.ajax({
      type: "GET",
      url: "./ty_qun_do.php?hmid="+ty_qun_hmid,
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