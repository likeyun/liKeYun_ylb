<!DOCTYPE html>
<html>
<head>
  <title>里客云活码管理系统安装程序 - v6.0</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.huoma.css">
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.theme.css">
  <style>
    input:-webkit-autofill, 
    textarea:-webkit-autofill, 
    select:-webkit-autofill { 
      -webkit-box-shadow: 0 0 0 1000px white inset; 
    }
    input[type=text]:focus, input[type=password]:focus, textarea:focus {
      -webkit-box-shadow: 0 0 0 1000px white inset; 
    }
  </style>
</head>
<body>
<!-- 顶部 -->
<div id="topbar">
  <span class="admin-title">里客云活码管理系统安装程序</span>
</div>

<!-- 主体 -->
<div class="container" style="width: 800px;">
  <br/>
  <br/>
  <div class="jumbotron" style="background: #f2f2f2;">
    <h2>里客云活码管理系统安装程序</h2>
    <p>这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。<br/><br/><strong>遇到安装问题，可以加入交流群研讨：<a href="javascript:;" data-toggle="modal" data-target="#myModal">点击加群</a></strong></p>

    <!-- 表单 -->
    <form onsubmit="return false" id="install">
    <div class="input-group mb-3">
      <div class="input-group-prepend dark">
        <span class="input-group-text">数据库地址</span>
      </div>
      <input type="text" class="form-control" placeholder="宝塔面板可填localhost，其他请按实际填写" id="dburl" name="dburl">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">数据库账号</span>
      </div>
      <input type="text" class="form-control" placeholder="数据库账号" id="dbuser" name="dbuser">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">数据库密码</span>
      </div>
      <input type="text" class="form-control" placeholder="数据库密码" id="dbpwd" name="dbpwd">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">数据库名称</span>
      </div>
      <input type="text" class="form-control" placeholder="数据库名称" id="dbname" name="dbname">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">管理员账号</span>
      </div>
      <input type="text" class="form-control" placeholder="管理员账号，用于登陆活码系统管理后台" id="user" name="user">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">管理员密码</span>
      </div>
      <input type="text" class="form-control" placeholder="管理员密码，用于登陆活码系统管理后台" id="pwd" name="pwd">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">管理员邮箱</span>
      </div>
      <input type="text" class="form-control" placeholder="管理员邮箱，用于找回密码等身份验证" id="email" name="email">
    </div>
    
    <!-- 安装按钮 -->
    <button type="submit" class="btn btn-tjzdy" style="margin:20px auto 0;display: block;" onclick="install();">开始安装</button>
  </form>
  </div>
</div>

<!-- 信息提示框 -->
<div id="alert" style="display: none;width: 770px;margin: 20px auto;"></div>
  

<!-- 模态框 -->
<div class="modal fade" id="myModal">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <!-- 模态框头部 -->
      <div class="modal-header">
        <h4 class="modal-title">加入交流群</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- 模态框主体 -->
      <div class="modal-body">
        <h5 style="text-align: center;">微信扫码进群</h5>
        <div style="width: 230px;margin:20px auto;background: #f00;">
          <img src="./qun_qrcode.png" width="230" />
        </div>
      </div>
      <!-- 模态框底部 -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
      </div>
 
    </div>
  </div>
</div>

<!-- JS -->
<script type="text/javascript">
// 延迟关闭信息提示框
function closesctips(){
  $("#alert").css('display','none');
}

// 安装
function install(){
  $.ajax({
      type: "POST",
      url: "./install_check.php",
      data: $('#install').serialize(),
      success: function (data) {
        // 安装成功
        if (data.code==100) {
          $(".container .jumbotron").html("<h3>liKeYun活码系统安装成功！</h3><p><a href='../console/' target='blank'>用户后台>&nbsp;&nbsp;&nbsp;</a> <a href='../dashboard/' target='blank'>管理后台> </a></p>");

        }else{
          $("#alert").css("display","block");
          $("#alert").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 安装失败
        $("#alert").css("display","block");
        $("#alert").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  setTimeout('closesctips()', 2000);
}
</script>
</body>
</html>