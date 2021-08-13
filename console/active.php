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
        $title = "引流宝 - 里客云开源活码系统";
        $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
        $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
        $favicon = "../images/favicon.png";
    }
  }else{
    $title = "引流宝 - 里客云开源活码系统";
    $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
    $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
    $favicon = "../images/favicon.png";
  }
?>
<!DOCTYPE html>
<html>
<head>
  <title>活动码 - <?php echo $title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/wangEditor.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.huoma.css">
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.theme.css">
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
  <h3>活动码管理</h3>
  <p>创建一个活动页面，描述活动详情，引导参与活动。</p>
  
  <!-- 左右布局 -->
  <!-- 左侧布局 -->
  <div class="left-nav">
    <button type="button" class="btn btn-zdy">活动码管理</button>
    <button type="button" class="btn btn-zdylight" data-toggle="modal" data-target="#addactive_modal">创建活动</button>
    <a href="./"><button type="button" class="btn btn-zdylight">返回首页</button></a>
  </div>';

  //计算总活码数量
  $sql_active = "SELECT * FROM huoma_active WHERE active_user='$lguser'";
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
  $sql_ldym = "SELECT * FROM huoma_yuming WHERE ym_type='2'";
  $result_ldym = $conn->query($sql_ldym);

  // 获取群活码列表
  $sql = "SELECT * FROM huoma_active WHERE active_user='$lguser' ORDER BY ID DESC limit {$offset},{$lenght}";
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
            $active_endtime = $row["active_endtime"];

            // 渲染到UI
            echo '<tr>';
              echo '<td class="td-title">'.$active_title.'</td>';
              if ($active_status == 1) {
                echo '<td class="td-status"><span class="badge badge-success">正常</span></td>';
              } else if ($active_status == 2) {
                echo '<td class="td-status"><span class="badge badge-danger">关闭</span></td>';
              } else if ($active_status == 3) {
                echo '<td class="td-status"><span class="badge badge-danger">封禁</span></td>';
              } else if ($active_status == 4) {
                echo '<td class="td-status"><span class="badge badge-danger">结束</span></td>';
              }
              echo '<td class="td-status">'.$active_update_time.'</td>';
              if (empty($active_endtime)) {
                echo '<td class="td-fwl">未设置</td>';
              }else{
                echo '<td class="td-fwl">'.$active_endtime.'</td>';
              }
              echo '<td class="td-fwl">'.$active_pv.'</td>
              <td class="td-caozuo" style="text-align: center;">
              <div class="btn-group dropleft">
              <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary" style="cursor:pointer;">•••</span></span>
              <div class="dropdown-menu">
              <a class="dropdown-item" href="./edi_active.php?activeid='.$active_id.'&home=ediactive&lang=zh_CN&token='.md5(uniqid()).'">编辑</a>
              <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#share_active" id="'.$active_id.'" onclick="shareactive(this);">分享</a>
              <a class="dropdown-item" href="javascript:;" id="'.$active_id.'" onclick="delactive(this);" title="点击后马上就删除的哦！">删除</a>
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
    echo '<div class="right-nav">暂无活码，请点击创建活码</div>';
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
          <button type="button" class="btn btn-tjzdy" data-dismiss="modal">关闭</button>
        </div>
   
      </div>
    </div>
  </div>
  
  <!-- 创建活动 -->
  <div class="modal fade" id="addactive_modal">
    <div class="modal-dialog">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">创建活动</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <!-- 标题 -->
          <form onsubmit="return false" id="addactive" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" name="active_title">
          </div>';

          // 落地页域名
          echo '<select class="form-control" name="active_yuming" style="-webkit-appearance:none;">
          <option value="">请选择落地页域名</option>';

          if ($result_ldym->num_rows > 0) {
            while($row_ldym = $result_ldym->fetch_assoc()) {
              $ldym = $row_ldym["yuming"];
              echo '<option value="'.$ldym.'">'.$ldym.'</option>';
            }
            // 同时也可以选择当前系统使用的域名
            echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
          }else{
            // 没有绑定落地页，使用当前系统使用的域名
            echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
          }
          echo '</select>';

          echo '<!-- 上传二维码 -->
          <div class="input-group mb-3" style="margin-top: 15px;">
            <input type="text" class="form-control wxqrcode" placeholder="上传二维码（用于活动结束后引流）" name="active_qrcode">
            <div class="input-group-append" style="cursor:pointer;position: relative;">
              <span class="input-group-text">
                <input type="file" id="select_wxqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
              </span>
            </div>
          </div>

          <!-- 结束说明 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">结束说明</span>
            </div>
            <input type="text" class="form-control" placeholder="活动结束后的说明文案" name="active_shuoming">
          </div>

          <!-- 结束时间 -->
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">结束时间</span>
            </div>
            <input type="text" class="form-control" placeholder="格式:xxxx-xx-xx（例:'.date("Y-m-d").'），可留空" name="active_endtime">
          </div>
          
          <!-- 活动展示形式 -->
          <select class="form-control" id="active_type" style="margin:15px 0;-webkit-appearance:none;" name="active_type">
            <option value="">请设置活动展示形式</option>
            <option value="1">跳转活动链接</option>
            <option value="2">展示活动文案</option>
          </select>

          <!-- 活动链接 -->
          <span id="active_url" style="display:none;">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">活动链接</span>
            </div>
            <input type="text" class="form-control" placeholder="请粘贴需要跳转的活动链接" name="active_url">
          </div>
          </span>

          <!-- 活动文案 -->
          <div id="active_wenan" style="display:none;margin-bottom:20px;"></div>
          <input type="hidden" name="active_content" id="active_content"/>
			
          <div class="upload_status"></div>

        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-tjzdy" onclick="gethtmlcontent();addactive();">立即创建</button>
        </div>
        </form>
   
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
  $("#addactive_modal .upload_status").css('display','none');
}

// 监听活动展示形式选项
$("#active_type").bind('input propertychange',function(e){
  // 获取当前点击的状态
  var active_type = $(this).val();
  // 1代表活动链接，2代表活动文案
  if (active_type == 1) {
    $("#active_url").css("display","block");
    $("#active_wenan").css("display","none");
  }else if (active_type == 2) {
    $("#active_url").css("display","none");
    $("#active_wenan").css("display","block");
  }
})

// 获取富文本编辑器的内容并且添加到表单中
function gethtmlcontent(){
  var htmlcontent = editor.txt.html();
  $("#active_content").val(htmlcontent);
}

// 创建活动码
function addactive(){
  $.ajax({
      type: "POST",
      url: "./add_active_do.php",
      data: $('#addactive').serialize(),
      success: function (data) {
        // 创建成功
        if (data.code==100) {
          $("#addactive_modal .upload_status").css("display","block");
          $("#addactive_modal .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#addwx_modal').modal('hide');
          // 刷新列表
          location.reload();
        }else{
          $("#addactive_modal .upload_status").css("display","block");
          $("#addactive_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 创建失败
        $("#addactive_modal .upload_status").css("display","block");
        $("#addactive_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
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
    var addwx_form = new FormData(document.getElementById("addactive"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:addwx_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#addactive_modal .upload_status").css("display","block");
          $("#addactive_modal .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#addactive_modal .wxqrcode").val(data.path);
          $("#addactive_modal .text").text("已上传");
        }else{
          $("#addactive_modal .upload_status").css("display","block");
          $("#addactive_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data+"</strong></div>");
        }
      },
      error:function(data){
        $("#addactive_modal .upload_status").css("display","block");
        $("#addactive_modal .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#addactive_modal .upload_status").css("display","block");
        $("#addactive_modal .upload_status").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
      }
    })
    // 关闭信息提示框
    setTimeout('closesctips()', 2000);
  }else{
    // console.log("等待上传");
  }
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
        $("#share_active .modal-body .qrcode").html("<img src='./qrcode.php?content="+data.url+"' width='200'/>");
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

// 富文本编辑器
const E = window.wangEditor
const editor = new E('#active_wenan')
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

// 创建编辑器
editor.create()

</script>
</body>
</html>