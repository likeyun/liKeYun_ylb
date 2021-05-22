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
  <title>编辑个人微信活码 - <?php echo $title; ?></title>
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

  // 获得wxid
  $wx_id = trim($_GET["wxid"]);

  // 获取当前wxid下的相关信息
  $sql_wx = "SELECT * FROM huoma_wx WHERE wx_id = '$wx_id'";
  $result_wx = $conn->query($sql_wx);
  if ($result_wx->num_rows > 0) {
    while($row_wx = $result_wx->fetch_assoc()) {
      $wx_title = $row_wx["wx_title"];
      $wx_status = $row_wx["wx_status"];
      $wx_yuming = $row_wx["yuming"];
      $wx_shuoming = $row_wx["wx_shuoming"];
      $wx_qrcode = $row_wx["wx_qrcode"];
      $wx_num = $row_wx["wx_num"];

      // 渲染数据到UI
      echo '<!-- 顶部导航栏 -->
      <div id="topbar">
        <span class="admin-title"><a href="./">'.$title.'</a></span>
        <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
      </div>

      <!-- 操作区 -->
      <div class="container">
        <br/>
        <h3>编辑微信活码</h3>
        <p>编辑微信活码</p>
        
        <!-- 左右布局 -->
        <!-- 电脑端横排列表 -->
        <div class="left-nav">
          <button type="button" class="btn btn-dark">编辑微信活码</button>
          <button type="button" class="btn btn-light"><a href="./wx.php?t=home/ediwx&lang=zh_CN&token='.md5(uniqid()).'">返回上一页</a></button>
          <button type="button" class="btn btn-light"><a href="./">返回首页</a></button>
        </div>

        <!-- 右侧布局 -->
        <div class="right-nav">
          <!-- 标题 -->
          <form onsubmit="return false" id="ediwx" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" value="'.$wx_title.'" name="wx_title">
          </div>';

          // 落地页域名
          echo '<select class="form-control" name="wx_yuming" style="-webkit-appearance:none;"><option value="'.$wx_yuming.'">落地页域名：'.$wx_yuming.'</option>';
            // 获取落地页域名
            $sql_ym = "SELECT * FROM huoma_yuming";
            $result_ym = $conn->query($sql_ym);
            // 遍历列表
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

          echo '<!-- 个人微信二维码 -->
          <div id="grwx_upload" style="margin:15px 0;"> 
            <div class="upload_byqun input-group mb-3">
              <input type="text" class="form-control wxqrcode" name="wx_qrcode" value="'.$wx_qrcode.'" placeholder="请上传个人微信二维码或粘贴图片地址">
              <div class="input-group-append" style="cursor:pointer;">
                <span class="input-group-text" style="cursor:pointer;position: relative;">
                  <input type="file" id="select_wxqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
                </span>
              </div>
            </div>
          </div>

          <!-- 标题 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">微信号</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入微信号" value="'.$wx_num.'" name="wx_num">
          </div>';

          echo '<!-- 活码状态 -->';
          echo '<select class="form-control" id="grwx_status" style="margin:15px 0;-webkit-appearance:none;" name="wx_status">';
          if ($wx_status == 3) {
            echo '<option>该活码因违规已被停止使用</option>';
          }else if ($wx_status == 1) {
            echo '<option value="1">正常使用</option><option value="2">暂停使用</option>';
          }else if($wx_status == 2) {
            echo '<option value="2">暂停使用</option><option value="1">正常使用</option>';
          }
          echo '</select>';

          echo '<!-- 说明文字 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">说明文字</span>
            </div>
            <input type="text" class="form-control" placeholder="备注、加微信要求等，不填则不显示" value="'.$wx_shuoming.'" name="wx_shuoming">
          </div>
          
          <!-- 隐藏域 -->
          <div>
            <input type="hidden" name="wx_id" value="'.$wx_id.'" />
          </div>';

          if ($wx_status !== '3') {
            echo '<!-- 提交按钮 -->
            <button type="button" class="btn btn-secondary" onclick="ediwx();">更新活码</button>';
          }
          
          echo '</form>
          
          <!-- 说明 -->
          <br/>
          <br/>
          <p>落地页域名：用户访问你的活码页面使用的域名。</p>
        </div>

      </div>';
    }
  }else{
    echo "<h1>参数错误！</h1>";
  }
}else{
  echo "未登录";
}
?>

<script>
// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}

// 编辑群活码
function ediwx(){
  $.ajax({
      type: "POST",
      url: "./edi_wx_do.php",
      data: $('#ediwx').serialize(),
      success: function (data) {
        // 更新成功
        if (data.code==100) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 刷新列表
          location.href="./wx.php";
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

// 上传个人微信二维码
var wxqrcode_lunxun = setInterval("upload_wxqrcode()",2000);
  function upload_wxqrcode() {
  var wxqrcode_filename = $("#select_wxqrcode").val();
  if (wxqrcode_filename) {
    clearInterval(wxqrcode_lunxun);
    var ediwx_form = new FormData(document.getElementById("ediwx"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:ediwx_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#grwx_upload .wxqrcode").val(data.path);
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