<?php
  // 页面字符编码
  header("Content-type:text/html;charset=utf-8");

  // 数据库配置
  include '../db_config/db_config.php';

  // 创建连接
  $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

  // 获取设置项
  $sql_set = "SELECT * FROM huoma_set";
  $result_set = $conn->query($sql_set);
  if ($result_set->num_rows > 0) {
    while($row_set = $result_set->fetch_assoc()) {
      $title = $row_set['title'];
      $keywords = $row_set['keywords'];
      $description = $row_set['description'];
      $favicon = $row_set['favicon'];
    }
    if ($title == null || empty($title) || $title == '') {
        $title = "里客云活码系统 - www.likeyuns.com";
        $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
        $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
        $favicon = "../images/favicon.png";
    }
  }else{
    $title = "里客云活码系统 - www.likeyuns.com";
    $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
    $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
    $favicon = "../images/favicon.png";
  }
?>
<!DOCTYPE html>
<html>
<head>
  <title>客服活码 - <?php echo $title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.huoma.css">
  <meta name="keywords" content="<?php echo $keywords; ?>">
  <meta name="description" content="<?php echo $description; ?>">
  <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon" />
</head>
<body>

<!-- 全局信息提示框 -->
<div id="Result" style="display: none;"></div>

<?php
// 判断登录状态
session_start();
if(isset($_SESSION["huoma.admin"])){

  // 当前登录的用户
  $lguser= $_SESSION["huoma.admin"];

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title">'.$title.'</span>
  <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">
  <br/>
  <h3>客服活码管理</h3>
  <p>创建、编辑、删除、分享微信客服活码</p>
  
  <!-- 左右布局 -->
  <!-- 左侧布局 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">活码管理</button>
    <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addwx_modal">创建活码</button>
    <a href="./"><button type="button" class="btn btn-light">返回首页</button></a>
  </div>';

  //计算总活码数量
  $sql_wx = "SELECT * FROM huoma_wx WHERE wx_user='$lguser'";
  $result_wx = $conn->query($sql_wx);
  $allwx_num = $result_wx->num_rows;

  //每页显示的活码数量
  $lenght = 10;

  //当前页码
  @$page = $_GET['p']?$_GET['p']:1;

  //每页第一行
  $offset = ($page-1)*$lenght;

  //总数页
  $allpage = ceil($allwx_num/$lenght);

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
  $sql = "SELECT * FROM huoma_wx WHERE wx_user='$lguser' ORDER BY ID DESC limit {$offset},{$lenght}";
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
              <th>访问</th>
              <th style="text-align: center;">操作</th>
            </tr>
          </thead>
          <tbody>';

          // 遍历数据
          while($row = $result->fetch_assoc()) {
            $wx_title = $row["wx_title"];
            $wx_id = $row["wx_id"];
            $wx_qrcode = $row["wx_qrcode"];
            $wx_num = $row["wx_num"];
            $wx_shuoming = $row["wx_shuoming"];
            $wx_update_time = $row["wx_update_time"];
            $wx_fwl = $row["wx_fwl"];
            $wx_status = $row["wx_status"];

            // 渲染到UI
            echo '<tr>';
              echo '<td class="td-title">'.$wx_title.'</td>';
              if ($wx_status == 1) {
                echo '<td class="td-status"><span class="badge badge-success">正常</span></td>';
              }else if ($wx_status == 2) {
                echo '<td class="td-status"><span class="badge badge-danger">关闭</span></td>';
              }else if ($wx_status == 3) {
                echo '<td class="td-status"><span class="badge badge-danger">封禁</span></td>';
              }
              echo '<td class="td-status">'.$wx_update_time.'</td>
              <td class="td-fwl">'.$wx_fwl.'</td>
              <td class="td-caozuo" style="text-align: center;">
              <div class="btn-group dropleft">
              <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary" style="cursor:pointer;">•••</span></span>
              <div class="dropdown-menu">
              <a class="dropdown-item" href="./edi_wx.php?wxid='.$wx_id.'&home=ediwx&lang=zh_CN&token='.md5(uniqid()).'">编辑</a>
              <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#share_wx" id="'.$wx_id.'" onclick="sharewx(this);">分享</a>
              <a class="dropdown-item" href="javascript:;" id="'.$wx_id.'" onclick="delwx(this);" title="点击后马上就删除的哦！">删除</a>
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
            echo '<li class="page-item"><a class="page-link" href="./wx.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./wx.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./wx.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./wx.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./wx.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./wx.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./wx.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';

  }else{
    echo '<div class="right-nav">暂无活码，请点击创建活码</div>';
  }

  echo '<!-- 分享模态框 -->
  <div class="modal fade" id="share_wx">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">分享微信活码</h4>
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
  
  <!-- 创建微信客服活码 -->
  <div class="modal fade" id="addwx_modal">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">创建微信客服活码</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <!-- 标题 -->
          <form onsubmit="return false" id="addwx" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" name="wx_title">
          </div>';

          // 落地页域名
          echo '<select class="form-control" name="wx_yuming" style="-webkit-appearance:none;">
          <option value="">请选择落地页域名</option>';

          if ($result_ym->num_rows > 0) {
            while($row_ym = $result_ym->fetch_assoc()) {
              $ym = $row_ym["yuming"];
              echo '<option value="'.$ym.'">'.$ym.'</option>';
            }
            // 同时也可以选择当前系统使用的域名
            echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
          }else{
            // 没有绑定落地页，使用当前系统使用的域名
            echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
          }
          echo '</select>';

          echo '<!-- 上传微信群二维码 -->
          <div class="input-group mb-3" style="margin-top: 15px;">
            <input type="text" class="form-control wxqrcode" placeholder="上传微信二维码或粘贴图片地址" name="wx_qrcode">
            <div class="input-group-append" style="cursor:pointer;position: relative;">
              <span class="input-group-text">
                <input type="file" id="select_wxqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
              </span>
            </div>
          </div>
          <!-- 微信号 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">微信号</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入微信号" name="wx_num">
          </div>

          <!-- 说明文字 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">说明文字</span>
            </div>
            <input type="text" class="form-control" placeholder="备注、加微信要求等，不填则不显示" name="wx_shuoming">
          </div>

          <!-- 说明文字 -->
          <div class="upload_status"></div>

          <!-- 提交 -->
          <button type="button" class="btn btn-dark" onclick="addwx();">立即创建</button>
          </form>

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
  $("#addwx_modal .upload_status").css('display','none');
}

//监听个人微信二维码的显示状态
$("#grwx_status").bind('input propertychange',function(e){
  //获取当前点击的状态
  var grwx_status = $(this).val();
  //如果开启备用群，则需要显示上传二维码和设置最大值
  if (grwx_status == 1) {
    $("#grwx_upload").css("display","block");
  }else if (grwx_status == 0) {
    //否则隐藏，不显示
    $("#grwx_upload").css("display","none");
  }
})

//监听备用微信群二维码的开启状态
$("#jwx_sm").bind('input propertychange',function(e){
  //获取当前点击的状态
  var grwx_status = $(this).val();
  //如果开启备用群，则需要显示上传二维码和设置最大值
  if (grwx_status == 1) {
    $("#jwx_sm_wenan").css("display","block");
  }else if (grwx_status == 0) {
    //否则隐藏，不显示
    $("#jwx_sm_wenan").css("display","none");
  }
})

// 创建微信活码
function addwx(){
  $.ajax({
      type: "POST",
      url: "./add_wx_do.php",
      data: $('#addwx').serialize(),
      success: function (data) {
        // 创建成功
        if (data.code==100) {
          $("#addwx_modal .upload_status").css("display","block");
          $("#addwx_modal .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#addwx_modal').modal('hide');
          // 刷新列表
          location.reload();
        }else{
          $("#addwx_modal .upload_status").css("display","block");
          $("#addwx_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 创建失败
        $("#addwx_modal .upload_status").css("display","block");
        $("#addwx_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}

// 上传微信二维码
var wxqrcode_lunxun = setInterval("upload_wxqrcode()",2000);
  function upload_wxqrcode() {
  var wxqrcode_filename = $("#select_wxqrcode").val();
  if (wxqrcode_filename) {
    clearInterval(wxqrcode_lunxun);
    var addwx_form = new FormData(document.getElementById("addwx"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:addwx_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#addwx_modal .upload_status").css("display","block");
          $("#addwx_modal .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#addwx_modal .wxqrcode").val(data.path);
          $("#addwx_modal .text").text("已上传");
        }else{
          $("#addwx_modal .upload_status").css("display","block");
          $("#addwx_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data+"</strong></div>");
        }
      },
      error:function(data){
        $("#addwx_modal .upload_status").css("display","block");
        $("#addwx_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#addwx_modal .upload_status").css("display","block");
        $("#addwx_modal .upload_status").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
      }
    })
    // 关闭信息提示框
    setTimeout('closesctips()', 2000);
  }else{
    // console.log("等待上传");
  }
}


// 删除微信活码
function delwx(event){
  // 获得当前点击的微信活码id
  var del_wxid = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_wx_do.php?wxid="+del_wxid,
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


// 分享微信活码
function sharewx(event){
  // 获得当前点击的微信活码id
  var share_wxid = event.id;
  $.ajax({
      type: "GET",
      url: "./share_wx_do.php?wxid="+share_wxid,
      success: function (data) {
        // 分享成功
        $("#share_wx .modal-body .link").text("链接："+data.url+"");
        $("#share_wx .modal-body .qrcode").html("<img src='./qrcode.php?content="+data.url+"' width='200'/>");
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
</script>
</body>
</html>