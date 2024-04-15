
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取插件
    getPlugin();
}

// 获取登录状态
function getLoginStatus(){
    
    // 获取
    $.ajax({
        type: "POST",
        url: "../login/getLoginStatus.php",
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
        
        if(adminStatus == 2) {
            
            // 非管理员
            noLimit('你的账号没有使用插件的权限');
        }else{
            
            // 显示按钮
            $('#button-view').css('display','block');
        }
    }else{
        
        // 隐藏按钮
        $('#button-view').css('display','none');
        
        // 跳转到登录页面
        jumpUrl('../login/');
    }
}

// 获取插件列表
function getPlugin(pageNum) {
    
    // 初始化
    initialize_getPlugin();
    
    $.ajax({
        type: "POST",
        url: "getPlugin.php",
        success: function(res){
            
            if(res.code == 200){

                // 遍历数据
                for (var i=0; i<res.pluginArray.length; i++) {

                    // 插件名称
                    var plugin_name = res.pluginArray[i].name;
                    
                    // 插件描述
                    var plugin_desc = res.pluginArray[i].desc;
                    
                    // 插件logo
                    var plugin_logo = res.pluginArray[i].logo;
                    
                    // 插件入口
                    var plugin_entry = res.pluginArray[i].entry;
                    
                    // 安装状态
                    var plugin_install = res.pluginArray[i].install;
                    
                    // 列表
                    if(plugin_install == 1) {
                        
                        // 未安装
                        var $plugin_HTML = $(
                        '<div class="plugin-card">'+
                        '    <div class="plugin-logo">'+
                        '        <img src="app/'+plugin_entry+'/'+plugin_logo+'" />'+
                        '    </div>'+
                        '    <div class="plugin-info">'+
                        '        <div class="plugin-name">'+plugin_name+'</div>'+
                        '        <div class="plugin-desc">'+plugin_desc+'</div>'+
                        '    </div>'+
                        '    <div class="plugin-btn">'+
                        '        <div class="btn-conf install-uninstall" data-toggle="modal" data-target="#anzhuangModal" id="'+plugin_entry+'" onclick="anzhuangPlugin(this);">安装插件</div>'+
                        '    </div>'+
                        '</div>'
                        );
                    }else {
                        
                        // 已安装
                        var $plugin_HTML = $(
                        '<div class="plugin-card">'+
                        '    <div class="plugin-logo">'+
                        '        <img src="app/'+plugin_entry+'/'+plugin_logo+'" />'+
                        '    </div>'+
                        '    <div class="plugin-info">'+
                        '        <div class="plugin-name">'+plugin_name+'</div>'+
                        '        <div class="plugin-desc">'+plugin_desc+'</div>'+
                        '    </div>'+
                        '    <div class="plugin-btn">'+
                        '        <div class="btn-conf install-uninstall" data-toggle="modal" data-target="#xiezaiModal" id="'+plugin_entry+'" onclick="xiezaiPlugin(this);">卸载插件</div>'+
                        '        <div class="btn-conf plugin_entry_data" id="'+plugin_entry+'" onclick="configAndUse(this);">使用插件</div>'+
                        '    </div>'+
                        '</div>'
                        );
                    }
                    $("#right .data-list").append($plugin_HTML);
                }
                
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
            }
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getPlugin.php');
      }
    });
}

// 询问是否要安装插件
function anzhuangPlugin(e) {
    
    var plugin_entry = e.id;
    $('#anzhuangModal .modal-footer').html(
        '<button class="default-btn" style="margin:0 auto;display:block;" id="'+plugin_entry+'" onclick="setup(this);"><span class="querenanzhuang">确认安装</span></button>'
    );
}

// 安装
function setup(e) {
    
    // 执行的安装文件
    const setupFile = 'app/' + e.id + '/server/setup.php';
    
    // 显示正在安装
    $('#anzhuangModal .modal-footer .querenanzhuang').text('正在安装插件...');
    
    $.ajax({
        type: "POST",
        url: setupFile,
        success: function(res){
            
            // 安装成功
            if(res.code == 200){
                
                setTimeout(function(){
                    
                    // 隐藏模态框
                    hideModal('anzhuangModal');
                    
                    // 显示结果
                    showNotification(res.msg);
                    
                    // 获取插件列表
                    getPlugin();
                },1500)
            }else{
                
                // 安装失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误');
        }
    });
}

// 询问是否要卸载插件
function xiezaiPlugin(e) {
    
    var plugin_entry = e.id;
    $('#xiezaiModal .modal-footer').html(
        '<button class="default-btn" style="margin:0 auto;display:block;" id="'+plugin_entry+'" onclick="uninstall(this);"><span class="querenxiezai">确认卸载</span></button>'
    );
}

// 卸载
function uninstall(e) {
    
    // 执行的卸载文件
    const uninstallFile = 'app/' + e.id + '/server/uninstall.php';
    
    // 显示正在卸载
    $('#xiezaiModal .modal-footer .querenxiezai').text('正在卸载插件...');
    
    // 执行卸载
    $.ajax({
        type: "POST",
        url: uninstallFile,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 卸载成功
                setTimeout(function(){
                    
                    // 隐藏模态框
                    hideModal('xiezaiModal');
                    
                    // 显示卸载结果
                    showNotification(res.msg);
                    
                    // 获取插件列表
                    getPlugin();
                },1500)
            }else{
                
                // 卸载失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误');
        }
    });
}

// 配置与使用
function configAndUse(e) {
    
    // 跳转到当前点击的插件管理页面
    const plugin_entry = e.id;
    location.href = 'app/' + plugin_entry;
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getSuCaiList(pageNum);
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

// 关闭顶部操作结果信息提示框
function hideTopAlert(){
    $('#topAlert').css('display','none');
    $("#topAlert").text('');
}

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
}

// 排查提示1
function showErrorResultForphpfileName(phpfileName){
    $('#app .result').html('<div class="error">服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+phpfileName+'的返回信息进行排查！<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a></div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 3000);
}

// 排查提示2
function errorPage(from,text){
    
    if(from == 'data-list'){
        
        $("#right .data-list").css('display','none');
        $("#right .data-card .loading").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
        $("#right .data-card .loading").css('display','block');
        
    }else if(from == 'qrcode-list'){

        $("#qunQrcodeListModal table").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }
    
}

// 无权限
function noLimit(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noLimit.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 暂无数据
function noData(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noRes.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化
function initialize_getPlugin(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list").empty('');
}

// 打开操作反馈（操作成功）
function showSuccessResult(content){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 打开操作反馈（操作失败）
function showErrorResult(content){
    $('#app .result').html('<div class="error">'+content+'</div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 关闭操作反馈
function hideResult(){
    $("#app .result .success").css("display","none");
    $("#app .result .error").css("display","none");
    $("#app .result .success").text('');
    $("#app .result .error").text('');
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