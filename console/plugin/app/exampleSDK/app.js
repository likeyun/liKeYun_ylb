
// 打开网页就是从这里开始执行代码
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取安装状态
    getSetupStatu();
    
    // 将data-list设置为显示
    $('#right .data-list').css('display','block');
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
function initialize_Login(loginStatus){
    
    if(loginStatus == 'login'){
        
        // 已登录
        // 显示按钮
        $('#button-view').css('display','block');
        
    }else{
        
        // 未登录
        // 隐藏按钮
        $('#button-view').css('display','none');
        
        // 跳转到登录页面
        jumpUrl('../../../login/');
    }
}

// 获取安装状态
function getSetupStatu() {
    
    $.ajax({
        type: "POST",
        url: "server/getSetupStatu.php",
        success: function(res){
            
            if(res.code == 200){
                
                // 未安装
                noData(res.msg);
            }else {
                
                // 这里可以正常显示内容
                // 可以在这里加入你的逻辑
                // 或者不加...
                // 或者加入获取默认数据的逻辑
                // 例如：渲染表格数据列表
                renderDataToDataTableList();
            }
        },
        error: function() {
            
            // 服务器发生错误
            noData('getSetupStatu.php服务器发生错误');
        }
    });
}

// 渲染数据到 data-table-list
function renderDataToDataTableList() {
    
    // 构建表头的HTML
    var $thead_HTML = $(
        '<tr>' +
        '   <th>标题</th>' +
        '   <th>发布时间</th>' +
        '   <th>访问量</th>' +
        '   <th>分类</th>' +
        '   <th>状态</th>' +
        '   <th style="text-align: right;">操作</th>' +
        '</tr>'
    );
    
    // 将表头的HTML渲染到 data-table-list <thead></thead>标签
    $("#right .data-table-list thead").html($thead_HTML);
    
    // 使用AJAX获取数据并渲染到 data-table-list <tbody></tbody>标签
    $.ajax({
        type: "POST",
        url: "server/getDataTableList.php",
        success: function(res){
            
            // 成功
            if(res.code == 200) {
                
                // 渲染
                if(res.dataList.length > 0) {
                    
                    // 循环
                    for (var i=0; i<res.dataList.length; i++) {
                        
                        // 解析json对象
                        const news_title = res.dataList[i].news_title;
                        const news_pv = res.dataList[i].news_pv;
                        const news_type = res.dataList[i].news_type;
                        const news_addtime = res.dataList[i].news_addtime;
                        const news_status = res.dataList[i].news_status;
                        
                        // 单独处理状态
                        if(news_status == 1) {
                            
                            var status_text = '正常';
                        }else {
                            
                            var status_text = '隐藏';
                        }
                        
                        // 构建tbody的HTML
                        var $tbody_HTML = $(
                            '<tr>' +
                            '   <td>'+news_title+'</td>' +
                            '   <td>'+news_addtime+'</td>' +
                            '   <td>'+news_pv+'</td>' +
                            '   <td>'+news_type+'</td>' +
                            '   <td>'+status_text+'</td>' +
                            '   <td style="text-align:right;">' +
                            '       <a href="server/edit.php">编辑</a>' +
                            '       <a href="server/del.php">删除</a>' +
                            '       <a href="server/share.php">分享</a>' +
                            '   </td>' +
                            '</tr>'
                        );
                        
                        // 将tbody的HTML渲染到data-table-list <tbody></tbody>标签
                        $("#right .data-table-list tbody").append($tbody_HTML);
                    }
                }
                
            }else {
                
                // 数据为空、获取失败的情况
                $("#right .data-table-list").html('数据获取失败！');
            }
            
        },
        error: function() {
            
            // 服务器发生错误
            $("#right .data-table-list").html('服务器发生错误！');
        }
    });
    
}

// 提交表单
function tijiaoForm() {
    
    $.ajax({
        type: "POST",
        url: "server/tijiaoForm.php",
        data: $('#form1').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200) {
                
                // 将提交结果显示在ret-text节点中
                // class="success"是提交成功的样式
                $('.ret-text').html('<p class="success">'+res.msg+'</p>');
                
                // 3秒后执行
                setTimeout(function(){
                    
                    // 刷新页面
                    location.reload();
                    
                    // 如果你想跳转到指定页面就用下面这个代码
                    // jumpUrl('https://www.qq.com');
                }, 3000);
            }else {
                
                // 将提交结果显示在ret-text节点中
                // class="error"是提交失败的样式
                $('.ret-text').html('<p class="error">'+res.msg+'</p>');
            }
            
        },
        error: function() {
            
            // 服务器发生错误
            $('.ret-text').html('<p class="error">服务器发生错误</p>');
        }
    });
    
    // 3.5秒后执行
    setTimeout(function(){
        
        // 清空操作结果
        cleanRetText();
    }, 3500);
}

// 模态框里面的提交表单
function createTijiao() {
    
    // 获取表单1和2的数据
    var modal_input1 = $('#createForm input[name="modal_input1"]').val();
    var modal_input2 = $('#createForm input[name="modal_input2"]').val();
    
    if(modal_input1 && modal_input2) {
        
        // 将提交结果显示在result节点
        $('#createModal .result').html('<p style="text-align:center;">表单1数据：' + modal_input1 + '，表单2数据：' + modal_input2 + '</p>')
        
        // 调用延时执行
        // 3秒后隐藏模态框
        setTimeout(function(){
            
            // 调用函数隐藏模态框
            hideModal('createModal');
        },3000)
    }else {
        
        $('#createModal .result').html('<p style="text-align:center;">表单不得留空</p>')
    }
    
}

// 注销登录
function exitLogin(){
    
    $.ajax({
        type: "POST",
        url: "../../../login/exitLogin.php",
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

// 隐藏Modal
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
}

// 清空操作结果
function cleanRetText() {
    $('.ret-text').html('');
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