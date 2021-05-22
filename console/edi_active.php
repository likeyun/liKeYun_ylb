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
  <title>编辑活动 - <?php echo $title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.huoma.css">
  <script src="../js/wangEditor.min.js"></script>
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

  // 获得activeid
  $active_id = trim($_GET["activeid"]);

  // 获取当前wxid下的相关信息
  $sql_active = "SELECT * FROM huoma_active WHERE active_id = '$active_id'";
  $result_active = $conn->query($sql_active);
  if ($result_active->num_rows > 0) {
    while($row_active = $result_active->fetch_assoc()) {
      $active_title = $row_active["active_title"];
      $active_status = $row_active["active_status"];
      $active_yuming = $row_active["active_yuming"];
      $active_shuoming = $row_active["active_shuoming"];
      $active_qrcode = $row_active["active_qrcode"];
      $active_type = $row_active["active_type"];
      $active_url = $row_active["active_url"];
      $active_content = $row_active["active_content"];
      $active_endtime = $row_active["active_endtime"];

      // 渲染数据到UI
      echo '<!-- 顶部导航栏 -->
      <div id="topbar">
        <span class="admin-title"><a href="./">'.$title.'</a></span>
        <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
      </div>

      <!-- 操作区 -->
      <div class="container">
        <br/>
        <h3>编辑活动码</h3>
        <p>对活动进行修改、编辑、更新，维护活动。</p>
        
        <!-- 左右布局 -->
        <!-- 电脑端横排列表 -->
        <div class="left-nav">
          <button type="button" class="btn btn-dark">编辑活动</button>
          <button type="button" class="btn btn-light"><a href="./active.php?home/ediactive&lang=zh_CN&token='.md5(uniqid()).'">返回上一页</a></button>
          <button type="button" class="btn btn-light"><a href="./">返回首页</a></button>
        </div>

        <!-- 右侧布局 -->
        <div class="right-nav">
          <!-- 标题 -->
          <form onsubmit="return false" id="ediactive" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" value="'.$active_title.'" name="active_title">
          </div>';

          // 落地页域名
          echo '<select class="form-control" name="active_yuming" style="-webkit-appearance:none;"><option value="'.$active_yuming.'">落地页域名：'.$active_yuming.'</option>';
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
              <input type="text" class="form-control wxqrcode" name="active_qrcode" value="'.$active_qrcode.'" placeholder="请上传个人微信二维码或粘贴图片地址">
              <div class="input-group-append" style="cursor:pointer;">
                <span class="input-group-text" style="cursor:pointer;position: relative;">
                  <input type="file" id="select_wxqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
                </span>
              </div>
            </div>
          </div>

          <!-- 结束说明 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">结束说明</span>
            </div>
            <input type="text" class="form-control" placeholder="活动结束后的说明文案" value="'.$active_shuoming.'" name="active_shuoming">
          </div>

          <!-- 结束时间 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">结束时间</span>
            </div>
            <input type="text" class="form-control" placeholder="格式:xxxx-xx-xx（例：'.date("Y-m-d").'），可留空" value="'.$active_endtime.'" name="active_endtime">
          </div>';

          echo '<!-- 活码状态 -->';
          echo '<select class="form-control" id="grwx_status" style="margin:15px 0;-webkit-appearance:none;" name="active_status">';
          
          if ($active_status == 3) {
            echo '<option>该活码因违规已被停止使用</option>';
          }else if ($active_status == 1) {
            echo '<option value="1">正常使用</option><option value="2">暂停使用</option><option value="4">活动结束</option>';
          }else if ($active_status == 2) {
            echo '<option value="2">暂停使用</option><option value="1">正常使用</option><option value="4">活动结束</option>';
          }else if ($active_status == 4) {
            echo '<option value="4">活动结束</option><option value="1">正常使用</option><option value="2">暂停使用</option>';
          }

          echo '</select>';

          echo '<!-- 活动展示形式 -->
          <select class="form-control" id="active_type" style="margin:15px 0;-webkit-appearance:none;" name="active_type">';
          if ($active_type == 1) {
            echo '<option value="1">跳转活动链接</option><option value="2">展示活动文案</option>';
          }else{
            echo '<option value="2">展示活动文案</option><option value="1">跳转活动链接</option>';
          }
          echo '</select>';

          echo '<!-- 活动链接 -->
          <span id="active_url" style="display:none;">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">活动链接</span>
            </div>
            <input type="text" class="form-control" value="'.$active_url.'" placeholder="请粘贴需要跳转的活动链接" name="active_url">
          </div>
          </span>

          <!-- 活动文案 -->
          <div id="active_con_div" style="display:none;margin-bottom:20px;">'.$active_content.'</div>
          <input type="hidden" name="active_content" id="active_content"/>

          <!-- 隐藏域 -->
          <div>
            <input type="hidden" name="active_id" value="'.$active_id.'" />
          </div>';

          if ($active_status !== '3') {
            echo '<!-- 提交按钮 -->
            <button type="button" class="btn btn-secondary" onclick="gethtmlcontent();ediactive();">更新活动</button>';
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

// 加载后监听活动展示形式
$(document).ready(function(){
  var active_type = '<?php echo $active_type; ?>';
  if (active_type == 1) {
    $("#active_url").css("display","block");
    $("#active_con_div").css("display","none");
  }else if (active_type == 2) {
    $("#active_url").css("display","none");
    $("#active_con_div").css("display","block");
  }
})

// 监听活动展示形式选项切换
$("#active_type").bind('input propertychange',function(e){
  // 获取当前点击的状态
  var active_type = $(this).val();
  // 1代表活动链接，2代表活动文案
  if (active_type == 1) {
    $("#active_url").css("display","block");
    $("#active_con_div").css("display","none");
  }else if (active_type == 2) {
    $("#active_url").css("display","none");
    $("#active_con_div").css("display","block");
  }
})

// 获取富文本编辑器的内容并且添加到表单中
function gethtmlcontent(){
  var htmlcontent = editor.txt.html();
  $("#active_content").val(htmlcontent);
}

// 编辑群活码
function ediactive(){
  $.ajax({
      type: "POST",
      url: "./edi_active_do.php",
      data: $('#ediactive').serialize(),
      success: function (data) {
        // 更新成功
        if (data.code==100) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 刷新列表
          location.href="./active.php";
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
    var ediwx_form = new FormData(document.getElementById("ediactive"));
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

// 富文本编辑器
const E = window.wangEditor
const editor = new E('#active_con_div')
// 配置菜单栏，删减菜单，调整顺序
editor.config.menus = [
    'head',
    'bold',
    'fontSize',
    'italic',
    'lineHeight',
    'foreColor',
    'backColor',
    'link',
    'justify',
    'image'
]

// 配置颜色（文字颜色、背景色）
editor.config.colors = [
  '#dc3545',
  '#28a745',
  '#ffc107',
  '#007bff',
  '#6c757d',
  '#302aa8',
  '#f211ba',
  '#576b95'
]

// 编辑器的字号
editor.config.fontSizes = {
'x-small': { name: '12px', value: '1' },
'small': { name: '15px', value: '2' },
'normal': { name: '16px', value: '3' },
'large': { name: '18px', value: '4' },
'x-large': { name: '24px', value: '5' },
'xx-large': { name: '32px', value: '6' },
'xxx-large': { name: '48px', value: '7' },
}

// 配置图片上传接口
editor.config.uploadImgServer = './upload.php';
editor.config.uploadFileName = 'file';

// 设置编辑区域高度为500px
editor.config.height = 500

// 创建编辑器
editor.create();

</script>
</body>
</html>