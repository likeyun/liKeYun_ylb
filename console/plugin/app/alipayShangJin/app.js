
// 打开网页就是从这里开始执行代码
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取安装状态
    getSetupStatu();
}

// 获取登录状态
function getLoginStatus(){
    
    // 获取
    $.ajax({
        type: "POST",
        url: "../../../login/getLoginStatus.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 已登录
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">'+res.user_name+' <span onclick="exitLogin();" class="exitLogin">退出</span></div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('login',res.user_admin)
            }else{
                
                // 未登录
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">未登录</div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('unlogin');
            }
        },
        error: function() {
            
            // 服务器发生错误
            errorPage('data-list','getLoginStatus.php');
        }
    });
}

// 登录初始化
function initialize_Login(loginStatus,adminStatus){
    
    if(loginStatus == 'login'){
        
        // 显示创建按钮
        $('#button-view').css('display','block');
        
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取安装状态
function getSetupStatu() {
    
    $.ajax({
        type: "POST",
        url: "server/getSetupStatu.php",
        success: function(res){
            
            // 显示data-list节点
            $('#right .data-list').css('display','block');
            
            if(res.code == 200){
                
                // 未安装
                noData(res.msg);
            }else {
                
                // 获取支付宝赏金配置信息
                getAlipayShangJinInfo();
            }
        },
        error: function() {
            
            // 服务器发生错误
            noData('getSetupStatu.php服务器发生错误');
        }
    });
}

// 获取支付宝赏金配置信息
function getAlipayShangJinInfo() {
    
    $.ajax({
        type: "GET",
        url: "server/alipayShangJin.json",
        success: function(res){
            
            $('input[name="alipayShangJinSPA_title"]').val(res.alipayShangJinSPA_title);
            $('input[name="alipayShangJinSPA_desc"]').val(res.alipayShangJinSPA_desc);
            $('input[name="alipayShangJinSPA_uid"]').val(res.alipayShangJinSPA_uid);
            $('input[name="alipayShangJinSPA_bgImg"]').val(res.alipayShangJinSPA_bgImg);
            $('input[name="alipayShangJinSPA_token"]').val(res.alipayShangJinSPA_token);
            $('input[name="alipayShangJinSPA_hbm"]').val(res.alipayShangJinSPA_hbm);
        },
        error: function() {
            
            // 失败
            $('.data-list').html('服务器发生错误！');
        }
    });
}

// 注销登录
function exitLogin(){
    
    $.ajax({
        type: "POST",
        url: "../login/exitLogin.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                location.reload();
            }
        },
        error: function() {
            
            // 服务器发生错误
            errorPage('data-list','exitLogin.php');
        }
    });
}

// 显示全局信息提示弹出提示
function showNotification(message) {
    
    // 获取文案
	$('#notification-text').text(message);
	
    // 计算文案长度并设置宽度
	var textLength = message.length * 25;
	$('#notification-text').css('width',textLength+'px');
	
    // 距离顶部的高度
	$('#notification').css('top', '25px');
	
    // 延迟隐藏
	setTimeout(function() {
		hideNotification();
	}, 3000);
}

// 隐藏全局信息提示弹出提示
function hideNotification() {
	var $notificationContainer = $('#notification');
	$notificationContainer.css('top', '-100px');
}

// 暂无数据
function noData(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../../../static/img/noData.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 获取URL参数
function queryURLParams(url) {
    var pattern = /(\w+)=(\w+)/ig;
    var parames = {};
    url.replace(pattern, ($, $1, $2) => {
        parames[$1] = $2;
    });
    return parames;
}