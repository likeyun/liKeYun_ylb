// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
  $("#add_qun_hm .upload_status").css('display','none');
  $("#edi_ffjq .upload_status").css('display','none');
}

// 创建付费群
function addffq(){
  $.ajax({
      type: "POST",
      url: "./creat_ffjq_do.php",
      data: $('#creatffq').serialize(),
      success: function (data) {
        // 创建成功
        if (data.code==100) {
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          setTimeout('location.reload()', 1000);
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


// 删除付费群
function delffq(event){
  // 获得当前点击的付费群id
  var ffqid = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_ffjq_do.php?ffqid="+ffqid,
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


// 分享付费群
function sharequn(event){
  // 获得当前点击的群活码id
  var ffjqid = event.id;
  $.ajax({
      type: "GET",
      url: "./share_qun_do.php?ffqid="+ffjqid,
      success: function (data) {
        // 分享成功
        $("#share_qun .modal-body .link").text("链接："+data.rkurl+"");
        $("#share_qun .modal-body .qrcode").html("<img src='../../../console/qrcode.php?content="+data.rkurl+"' width='200'/>");
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

// 编辑付费群，获取群信息
function ediffq_getinfo(event){
  // 获得当前点击的id
  var ffjqid = event.id;
  $.ajax({
      type: "GET",
      url: "./edi_ffjq_getinfo.php?ffqid="+ffjqid,
      success: function (data) {

        // 获取标题、价格、id
        $("#ffjqtitle").val(data.ffjq_title);
        $("#ffjqprice").val(data.ffjq_price);
        $("#ffqid").val(ffjqid)

        // 获取已选择的入口域名
        $("#select_rkym .form-control").empty();
        $("#select_ldym .form-control").empty();
        $("#select_rkym .form-control").append("<option value='"+data.ffjq_rkym+"'>"+data.ffjq_rkym+"</option>");
        // 获取域名库中的入口域名
        getymlist('1');
        // 获取已选择的落地域名
        $("#select_ldym .form-control").append("<option value='"+data.ffjq_ldym+"'>"+data.ffjq_ldym+"</option>");
        // 获取域名库中的落地域名
        getymlist('2');

        // 获取群
        $("#ediffqqrcode").val(data.ffjq_qrcode);
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

// 获取域名库中的入口域名
function getymlist(ymtype){
  $.ajax({
      type: "GET",
      url: "./getymlist.php?ymtype="+ymtype,
      success: function (data) {
        if (ymtype == 1) {
          for (var i = 0; i <= data.length; i++) {
            $("#select_rkym .form-control").append("<option value='"+data[i].yuming+"'>"+data[i].yuming+"</option>");
          }
        }else{
          for (var i = 0; i <= data.length; i++) {
            $("#select_ldym .form-control").append("<option value='"+data[i].yuming+"'>"+data[i].yuming+"</option>");
          }
        }
      },
      error : function() {
        alert("获取域名列表失败")
      }
  });
}

// 提交编辑
function ediffq(){
  $.ajax({
      type: "POST",
      url: "./edi_ffjq_do.php",
      data: $('#ediffjq').serialize(),
      success: function (data) {
        // 创建成功
        if (data.code==100) {
          $("#edi_ffjq .upload_status").css("display","block");
          $("#edi_ffjq .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 刷新列表
          setTimeout('location.reload()', 1000);
        }else{
          $("#edi_ffjq .upload_status").css("display","block");
          $("#edi_ffjq .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 创建失败
        $("#edi_ffjq .upload_status").css("display","block");
        $("#edi_ffjq .upload_status").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 上传微信群二维码（创建）
var qunqrcode_lunxun = setInterval("upload_qunqrcode()",2000);
  function upload_qunqrcode() {
  var wxqrcode_filename = $("#select_qunqrcode").val();
  if (wxqrcode_filename) {
    clearInterval(qunqrcode_lunxun);
    var creatffq = new FormData(document.getElementById("creatffq"));
    $.ajax({
      url:"../../../console/upload.php",
      type:"post",
      data:creatffq,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#qun_upload .form-control").val(data.path);
          $("#select_qunqrcode .text").text("已上传");
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
  }
}


// 上传微信群二维码（编辑）
var edi_qunqrcode_lunxun = setInterval("edi_upload_qunqrcode()",2000);
  function edi_upload_qunqrcode() {
  var qunqrcode_filename = $("#edi_select_qunqrcode").val();
  if (qunqrcode_filename) {
    clearInterval(edi_qunqrcode_lunxun);
    var ediffjq = new FormData(document.getElementById("ediffjq"));
    $.ajax({
      url:"../../../console/upload.php",
      type:"post",
      data:ediffjq,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#edi_ffjq .upload_status").css("display","block");
          $("#edi_ffjq .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#ediffqqrcode").val(data.path);
          $("#edi_select_qunqrcode .text").text("已上传");
        }else{
          $("#edi_ffjq .upload_status").css("display","block");
          $("#edi_ffjq .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error:function(data){
        $("#edi_ffjq .upload_status").css("display","block");
        $("#edi_ffjq .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#edi_ffjq .upload_status").css("display","block");
        $("#edi_ffjq .upload_status").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
      }
    })
    // 关闭信息提示框
    setTimeout('closesctips()', 2000);
  }
}


// 状态切换
function status_do(event){
	var status = event.id.split("-")[1];
	var ffqid = event.id.match(/(\S*)-/)[1];
	$.ajax({
      type: "GET",
      url: "./edi_status_do.php?ffqid="+ffqid+"&status="+status,
      success: function (data) {
        // 成功
        location.reload();
      },
      error : function() {
        // 失败
        alert("服务器发生错误")
      }
  });
}

// 安装插件
function ins_add(){
  $.ajax({
      type: "POST",
      url: "./install.php",
      success: function (data) {
        // 成功
        if (data.code == 100) {
          $(".right-nav .success").css('display','block');
          setTimeout('location.reload()',2000);
        }else{
          alert(data.msg)
        }
        
      },
      error : function() {
        // 失败
        alert("服务器发生错误")
      }
  });
}