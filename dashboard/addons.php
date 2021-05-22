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
  <meta name="keywords" content="<?php echo $keywords; ?>">
  <meta name="description" content="<?php echo $description; ?>">
  <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon" />
</head>
<body>

<!-- 全局信息提示框 -->
<div id="Result" style="display: none;"></div>

<?php
// 页面字符编码
header("Content-type:text/html;charset=utf-8");

// 数据库配置
include '../db_config/db_config.php';

// 创建连接
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
  
// 判断登录状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

  // 当前登录的用户
  $lguser= $_SESSION["huoma.dashboard"];

  include '../db_config/VersionCheck.php';

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title"><a href="./">里客云开源活码系统</a></span>
  <span class="admin-login-link"><a href="./account/exit">'.$_SESSION["huoma.dashboard"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">';
  echo '<br/>
  <h3>插件中心</h3> 
  <p>查看、管理、使用、配置你安装的插件</p>

  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">';
    echo '<a href="#"><button type="button" class="btn btn-dark">插件中心</button></a>';
    // 遍历插件列表
    function Get_Addons_Dir($addons_path){
      if(is_dir($addons_path)){
        $addons_dir = scandir($addons_path);
        foreach ($addons_dir as $value){
          // 获得该目录下的文件
          if($value == '.' || $value == '..'){
            continue;
          }else if(is_dir($addons_path)){
            // 获得该目录下的addons_config配置文件的addons_title
            $addons_config = $addons_path.'/'.$value.'/addons_config.json';
            $addons_config_post = file_get_contents($addons_config,true);
            $addons_title_arr = json_decode($addons_config_post,true);
            $addons_title = $addons_title_arr["title"];
            $addons_admin = $addons_path.'/'.$value.'/admin/';
            echo '<a href="'.$addons_admin.'"><button type="button" class="btn btn-light">'.$addons_title.'</button></a>';
          }
        }
      }
      echo '<a href="../dashboard/"><button type="button" class="btn btn-light">返回首页</button></a>';
    }
    $addons_path = '../addons';
    Get_Addons_Dir($addons_path); 
  echo '</div>';

  echo '<!-- 右侧布局 -->
  <div class="right-nav">
    <div class="jumbotron" style="padding:30px 20px 10px 20px;">
      <h2><b>liKeYun活码系统插件中心</b></h2> 
      <p>插件中心，可以便于开发者自行开发插件或安装开源作者提供的免费和付费插件，根据个人需求安装插件。</p> 
      <h5><b>如何安装插件？</b></h5>
      <p>只需要将插件程序包复制到活码系统根目录下的addons目录即可，一般无需安装和配置，复制到目录即可生效使用。</p>
      <h5><b>如何获得插件？</b></h5>
      <p>目前不设插件市场，插件均由开发者开发后，自行宣传，或在官方群留意作者发布的插件，如有需求，也可以联系作者或有开发能力的开发者付费定制插件。</p>
    </div>
  </div>';

}else{
  // 跳转到登陆界面
  header("Location:../account/login/");
}
?>
  
  <!-- 创建群活码 -->
  <div class="modal fade" id="add_qun_hm">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">创建群活码</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">

          <!-- 标题 -->
          <form role="form" action="##" onsubmit="return false" method="post" id="addqun" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" name="qun_title">
          </div>

          <!-- 落地页域名 -->
          <select class="form-control" name="qun_yuming" style="-webkit-appearance:none;">
            <option value="">请选择落地页域名</option>
            <?php
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
            ?>
          </select>

          <!-- 是否显示个人微信 -->
          <select class="form-control" id="grwx_status" style="margin:15px 0;-webkit-appearance:none;" name="wx_status">
            <option value="">是否显示个人微信</option>
            <option value="1">显示</option>
            <option value="2">隐藏</option>
          </select>

          <!-- 个人微信二维码 -->
          <div id="grwx_upload" style="display:none;"> 
            <div class="upload_byqun input-group mb-3">
              <input type="text" class="form-control" name="wx_qrcode" placeholder="请上传个人微信二维码或粘贴图片地址">
              <!-- 上传个人微信按钮 -->
              <div class="input-group-append" style="cursor:pointer;position: relative;">
                <span class="input-group-text">
                  <input type="file" id="select_wxqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
                </span>
              </div>
            </div>
          </div>
          </form>

          <div class="upload_status"></div>

          <!-- 说明 -->
          <p>创建完成后，点击 <span class="badge badge-secondary">•••</span> 编辑，上传群二维码</p>

        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="addqun();">创建群活码</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
        </div>
   
      </div>
    </div>
  </div>


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
  $("#add_qun_hm .upload_status").css('display','none');
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

//监听个人微信二维码的显示状态
$("#grwx_select .form-control").bind('input propertychange',function(e){
  alert("666")
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
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#add_qun_hm').modal('hide');
          // 刷新列表
          location.reload();
        }else{
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 创建失败
        $("#add_qun_hm .upload_status").css("display","block");
        $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
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
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#grwx_upload .form-control").val(data.path);
          $("#grwx_upload .text").text("已上传");
        }else{
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error:function(data){
        $("#add_qun_hm .upload_status").css("display","block");
        $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#add_qun_hm .upload_status").css("display","block");
        $("#add_qun_hm .upload_status").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
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