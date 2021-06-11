// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
}

// 更新账号信息
function ediuser(){
  $.ajax({
      type: "POST",
      url: "./edi_user_do.php",
      data: $('#ediuser').serialize(),
      success: function (data) {
        // 更新成功
        if (data.code==100) {
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
        // 更新失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}

// 暂未开通
function no_wxpay(){
  alert("暂未开通微信支付通道");
}

function no_alipay(){
  alert("暂未开通支付宝通道");
}

// PayJs微信支付
function payjs_wxpay(){
  // 获取套餐数据
  var taocan_data = $("#select_taocan").find("option:selected").val();
  // 获取user_id
  var user_id_data = $("#user_id").val();
  if (taocan_data == '') {
    $("#Result").css("display","block");
    $("#Result").html("<div class=\"alert alert-danger\"><strong>请选择续费的套餐</strong></div>");
  }else{
    // 发起支付请求
    $.ajax({
      type: "GET",
      url: "../pay/payjs/wxpay.php?taocan="+taocan_data+"&userid="+user_id_data,
      success: function (data) {
        // 请求成功
        if (data.return_msg=='SUCCESS') {
          // 把二维码展示区域显示出来
          $("#xufei_modal .pay_content").css("display","block");
          $("#xufei_modal .paytips").text("请扫码完成支付");
          $("#xufei_modal .pay_content .pay_qrcode").html("<img src='"+data.qrcode+"' />");
          $("#order_no").text(data.out_trade_no); // 把订单号展示在页面上
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务异常</strong></div>");
        }
      },
      error : function(data) {
        // 请求失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务发生错误</strong></div>");
      },
      beforeSend : function(data){
        // 把二维码展示区域显示出来
        $("#xufei_modal .pay_content").css("display","block");
        $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../images/loading.gif' style='width:50px;height:50px;margin:80px auto;display:block;'/>");
      }
    });
  }
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);

  // 轮询订单支付状态
  var check_pay_num = 0;
  var check_pay_status = setInterval(function(){
    check_pay_num += 1;
    if (check_pay_num == 60) {
      // 请求60次后超时
      console.log("超时");
      // 停止轮询
      clearInterval(check_pay_status);
    }else{
      // 获取订单号
      var order_no = $("#order_no").text();
      $.ajax({
          type: "GET",
          url: "../pay/payjs/checkpay.php?order_no=" + order_no,
          async: true,
          dataType:"json",
          success: function(data){
            if (data.code == 200) {
              $("#xufei_modal .pay_content .pay_qrcode").html("<img src=\"../images/wxpay_success.png\" />");
              $("#xufei_modal .paytips").text("支付成功");
              console.log("支付完成");
              // 停止轮询
              clearInterval(check_pay_status);
            }else{
              console.log("等待支付...");
            }
          }
      });
    }
  }, 2000);
}

// 小叮当微信支付
function xdd_wxpay(){
  // 获取套餐数据
  var taocan_data = $("#select_taocan").find("option:selected").val();
  // 获取user_id
  var user_id_data = $("#user_id").val();
  if (taocan_data == '') {
    $("#Result").css("display","block");
    $("#Result").html("<div class=\"alert alert-danger\"><strong>请选择续费的套餐</strong></div>");
  }else{
    // 发起支付请求
    $.ajax({
      type: "GET",
      url: "../pay/xdd/xddpay.php?taocan="+taocan_data+"&userid="+user_id_data+"&paytype=44",
      success: function (data) {
        // 请求成功
        if (data.pay_type=='weixin') {
          // 把二维码展示区域显示出来
          $("#xufei_modal .pay_content").css("display","block");
          $("#xufei_modal .paytips").text("请扫码完成支付");
          $("#xufei_modal .pay_content .pay_qrcode").html("<img src='"+data.qr_img+"' />");
          $("#order_no").text(data.xddpay_order); // 把订单号展示在页面上
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务异常</strong></div>");
        }
      },
      error : function(data) {
        // 请求失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务发生错误</strong></div>");
      },
      beforeSend : function(data){
        // 把二维码展示区域显示出来
        $("#xufei_modal .pay_content").css("display","block");
        $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../images/loading.gif' style='width:50px;height:50px;margin:80px auto;display:block;'/>");
      }
    });
  }
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);

  // 轮询订单支付状态
  var check_pay_num = 0;
  var check_pay_status = setInterval(function(){
    check_pay_num += 1;
    if (check_pay_num == 60) {
      // 请求60次后超时
      console.log("超时");
      // 停止轮询
      clearInterval(check_pay_status);
    }else{
      // 获取订单号
      var order_no = $("#order_no").text();
      $.ajax({
          type: "GET",
          url: "../pay/xdd/checkpay.php?order_no="+order_no,
          async: true,
          dataType:"json",
          success: function(data){
            if (data.code == 200) {
              $("#xufei_modal .pay_content .pay_qrcode").html("<img src=\"../images/wxpay_success.png\" />");
              $("#xufei_modal .paytips").text("支付成功");
              console.log("支付完成");
              // 停止轮询
              clearInterval(check_pay_status);
            }else{
              console.log("等待支付...");
            }
          }
      });
    }
  }, 2000);
}


// PayJs支付宝
function payjs_alipay(){
  // 获取套餐数据
  var taocan_data = $("#select_taocan").find("option:selected").val();
  // 获取user_id
  var user_id_data = $("#user_id").val();
  if (taocan_data == '') {
    $("#Result").css("display","block");
    $("#Result").html("<div class=\"alert alert-danger\"><strong>请选择续费的套餐</strong></div>");
  }else{
    // 发起支付请求
    $.ajax({
      type: "GET",
      url: "../pay/payjs/alipay.php?taocan="+taocan_data+"&userid="+user_id_data,
      success: function (data) {
        // 请求成功
        if (data.return_msg=='SUCCESS') {
          // 把二维码展示区域显示出来
          $("#xufei_modal .pay_content").css("display","block");
          $("#xufei_modal .paytips").text("请扫码完成支付");
          $("#xufei_modal .pay_content .pay_qrcode").html("<img src='"+data.qrcode+"' />");
          $("#order_no").text(data.out_trade_no); // 把订单号展示在页面上
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务异常</strong></div>");
        }
      },
      error : function(data) {
        // 请求失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务发生错误</strong></div>");
      },
      beforeSend : function(data){
        // 把二维码展示区域显示出来
        $("#xufei_modal .pay_content").css("display","block");
        $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../images/loading.gif' style='width:50px;height:50px;margin:80px auto;display:block;'/>");
      }
    });
  }
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);

  // 轮询订单支付状态
  var check_pay_num = 0;
  var check_pay_status = setInterval(function(){
    check_pay_num += 1;
    if (check_pay_num == 60) {
      // 请求60次后超时
      console.log("超时");
      // 停止轮询
      clearInterval(check_pay_status);
    }else{
      // 获取订单号
      var order_no = $("#order_no").text();
      $.ajax({
          type: "GET",
          url: "../pay/payjs/checkpay.php?order_no=" + order_no,
          async: true,
          dataType:"json",
          success: function(data){
            if (data.code == 200) {
              $("#xufei_modal .pay_content .pay_qrcode").html("<img src=\"../images/alipay_success.png\" />");
              $("#xufei_modal .paytips").text("支付成功");
              console.log("支付完成");
              // 停止轮询
              clearInterval(check_pay_status);
            }else{
              console.log("等待支付...");
            }
          }
      });
    }
  }, 2000);
}


// 小叮当支付宝
function xdd_alipay(){
  // 获取套餐数据
  var taocan_data = $("#select_taocan").find("option:selected").val();
  // 获取user_id
  var user_id_data = $("#user_id").val();
  if (taocan_data == '') {
    $("#Result").css("display","block");
    $("#Result").html("<div class=\"alert alert-danger\"><strong>请选择续费的套餐</strong></div>");
  }else{
    // 发起支付请求
    $.ajax({
      type: "GET",
      url: "../pay/xdd/xddpay.php?taocan="+taocan_data+"&userid="+user_id_data+"&paytype=43",
      success: function (data) {
        // 请求成功
        if (data.pay_type=='alipay') {
          // 把二维码展示区域显示出来
          $("#xufei_modal .pay_content").css("display","block");
          $("#xufei_modal .paytips").text("请扫码完成支付");
          $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../console/qrcode.php?content="+data.qr+"' />");
          $("#order_no").text(data.xddpay_order); // 把订单号展示在页面上
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务异常</strong></div>");
        }
      },
      error : function(data) {
        // 请求失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务发生错误</strong></div>");
      },
      beforeSend : function(data){
        // 把二维码展示区域显示出来
        $("#xufei_modal .pay_content").css("display","block");
        $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../images/loading.gif' style='width:50px;height:50px;margin:80px auto;display:block;'/>");
      }
    });
  }
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);

  // 轮询订单支付状态
  var check_pay_num = 0;
  var check_pay_status = setInterval(function(){
    check_pay_num += 1;
    if (check_pay_num == 60) {
      // 请求60次后超时
      console.log("超时");
      // 停止轮询
      clearInterval(check_pay_status);
    }else{
      // 获取订单号
      var order_no = $("#order_no").text();
      $.ajax({
          type: "GET",
          url: "../pay/xdd/checkpay.php?order_no=" + order_no,
          async: true,
          dataType:"json",
          success: function(data){
            if (data.code == 200) {
              $("#xufei_modal .pay_content .pay_qrcode").html("<img src=\"../images/alipay_success.png\" />");
              $("#xufei_modal .paytips").text("支付成功");
              console.log("支付完成");
              // 停止轮询
              clearInterval(check_pay_status);
            }else{
              console.log("等待支付...");
            }
          }
      });
    }
  }, 2000);
}


// 支付宝当面付
function dmf_alipay(){
  // 获取套餐数据
  var taocan_data = $("#select_taocan").find("option:selected").val();
  // 获取user_id
  var user_id_data = $("#user_id").val();
  if (taocan_data == '') {
    $("#Result").css("display","block");
    $("#Result").html("<div class=\"alert alert-danger\"><strong>请选择续费的套餐</strong></div>");
  }else{
    // 发起支付请求
    $.ajax({
      type: "GET",
      url: "../pay/alipay/alipay.php?taocan="+taocan_data+"&userid="+user_id_data,
      success: function (data) {
        // 请求成功
        if (data.code=='200') {
          // 把二维码展示区域显示出来
          $("#xufei_modal .pay_content").css("display","block");
          $("#xufei_modal .paytips").text("请扫码完成支付");
          $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../console/qrcode.php?content="+data.qrcode+"' />");
          $("#order_no").text(data.order_no); // 把订单号展示在页面上
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务异常</strong></div>");
        }
      },
      error : function(data) {
        // 请求失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务发生错误</strong></div>");
      },
      beforeSend : function(data){
        // 把二维码展示区域显示出来
        $("#xufei_modal .pay_content").css("display","block");
        $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../images/loading.gif' style='width:50px;height:50px;margin:80px auto;display:block;'/>");
      }
    });
  }
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);

  // 轮询订单支付状态
  var check_pay_num = 0;
  var check_pay_status = setInterval(function(){
    check_pay_num += 1;
    if (check_pay_num == 60) {
      // 请求60次后超时
      console.log("超时");
      // 停止轮询
      clearInterval(check_pay_status);
    }else{
      // 获取订单号
      var order_no = $("#order_no").text();
      $.ajax({
          type: "GET",
          url: "../pay/alipay/checkpay.php?order_no="+order_no,
          async: true,
          dataType:"json",
          success: function(data){
            if (data.code == 200) {
              $("#xufei_modal .pay_content .pay_qrcode").html("<img src=\"../images/alipay_success.png\" />");
              $("#xufei_modal .paytips").text("支付成功");
              console.log("支付完成");
              // 停止轮询
              clearInterval(check_pay_status);
            }else{
              console.log("等待支付...");
            }
          }
      });
    }
  }, 2000);
}

// 易支付微信支付
function easy_wxpay(){
  // 获取套餐数据
  var taocan_data = $("#select_taocan").find("option:selected").val();
  // 获取user_id
  var user_id_data = $("#user_id").val();
  if (taocan_data == '') {
    $("#Result").css("display","block");
    $("#Result").html("<div class=\"alert alert-danger\"><strong>请选择续费的套餐</strong></div>");
  }else{
    // 发起支付请求
    $.ajax({
      type: "GET",
      url: "../pay/easypay/wxpay.php?taocan="+taocan_data+"&userid="+user_id_data,
      success: function (data) {
        // 请求成功
        if (data.code=='200') {

          // 易支付，打开新页面扫码支付
          window.open(data.url);

          $("#order_no").text(data.order_no); // 把订单号展示在页面上
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务异常</strong></div>");
        }
      },
      error : function(data) {
        // 请求失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务发生错误</strong></div>");
      },
      beforeSend : function(data){
        // 把二维码展示区域显示出来
        $("#xufei_modal .pay_content").css("display","block");
        $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../images/loading.gif' style='width:50px;height:50px;margin:80px auto;display:block;'/>");
      }
    });
  }
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);

  // 轮询订单支付状态
  var check_pay_num = 0;
  var check_pay_status = setInterval(function(){
    check_pay_num += 1;
    if (check_pay_num == 60) {
      // 请求60次后超时
      console.log("超时");
      // 停止轮询
      clearInterval(check_pay_status);
    }else{
      // 获取订单号
      var order_no = $("#order_no").text();
      $.ajax({
          type: "GET",
          url: "../pay/easypay/checkpay.php?order_no="+order_no,
          async: true,
          dataType:"json",
          success: function(data){
            if (data.code == 200) {
              $("#xufei_modal .pay_content .pay_qrcode").html("<img src=\"../images/wxpay_success.png\" />");
              $("#xufei_modal .paytips").text("支付成功");
              console.log("支付完成");
              // 停止轮询
              clearInterval(check_pay_status);
            }else{
              console.log("等待支付...");
            }
          }
      });
    }
  }, 2000);
}


// 易支付支付宝
function easy_alipay(){
  // 获取套餐数据
  var taocan_data = $("#select_taocan").find("option:selected").val();
  // 获取user_id
  var user_id_data = $("#user_id").val();
  if (taocan_data == '') {
    $("#Result").css("display","block");
    $("#Result").html("<div class=\"alert alert-danger\"><strong>请选择续费的套餐</strong></div>");
  }else{
    // 发起支付请求
    $.ajax({
      type: "GET",
      url: "../pay/easypay/alipay.php?taocan="+taocan_data+"&userid="+user_id_data,
      success: function (data) {
        // 请求成功
        if (data.code=='200') {

          // 易支付，打开新页面扫码支付
          window.open(data.url);

          $("#order_no").text(data.order_no); // 把订单号展示在页面上
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务异常</strong></div>");
        }
      },
      error : function(data) {
        // 请求失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>支付服务发生错误</strong></div>");
      },
      beforeSend : function(data){
        // 把二维码展示区域显示出来
        $("#xufei_modal .pay_content").css("display","block");
        $("#xufei_modal .pay_content .pay_qrcode").html("<img src='../images/loading.gif' style='width:50px;height:50px;margin:80px auto;display:block;'/>");
      }
    });
  }
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);

  // 轮询订单支付状态
  var check_pay_num = 0;
  var check_pay_status = setInterval(function(){
    check_pay_num += 1;
    if (check_pay_num == 60) {
      // 请求60次后超时
      console.log("超时");
      // 停止轮询
      clearInterval(check_pay_status);
    }else{
      // 获取订单号
      var order_no = $("#order_no").text();
      $.ajax({
          type: "GET",
          url: "../pay/easypay/checkpay.php?order_no="+order_no,
          async: true,
          dataType:"json",
          success: function(data){
            if (data.code == 200) {
              $("#xufei_modal .pay_content .pay_qrcode").html("<img src=\"../images/alipay_success.png\" />");
              $("#xufei_modal .paytips").text("支付成功");
              console.log("支付完成");
              // 停止轮询
              clearInterval(check_pay_status);
            }else{
              console.log("等待支付...");
            }
          }
      });
    }
  }, 2000);
}


// 邀请码续费
function yqm_xufei(){
  $.ajax({
      type: "POST",
      url: "./yqm_xufei_do.php",
      data: $('#xufei').serialize(),
      success: function (data) {
        // 续费成功
        if (data.code==100) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#addwx_modal').modal('hide');
          // 刷新列表
          location.reload();
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 续费失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}