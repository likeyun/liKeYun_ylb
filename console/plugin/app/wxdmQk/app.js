// 打开网页就是从这里开始执行代码
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取安装状态
    getSetupStatu();
    
    // clipboard插件
    var clipboard = new ClipboardJS('#shareDataModal .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#shareDataModal .modal-footer button').text('已复制');
    });
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
                
                // 获取页码
                var pageNum = queryURLParams(window.location.href).p;
                
                if(pageNum !== 'undefined'){
                    
                    // 获取当前页码数据列表
                    getDataList(pageNum);
                }else{
                    
                    // 获取首页
                    getDataList();
                }
            }
        },
        error: function() {
            
            // 服务器发生错误
            noData('getSetupStatu.php服务器发生错误');
        }
    });
}

// 获取外部跳微信卡片列表
function getDataList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "server/getDataList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "server/getDataList.php?p="+pageNum
        
        // 设置URL路由
        setRouter(pageNum);
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getDataList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>ID</th>' +
                '   <th>标题</th>' +
                '   <th>目标链接</th>' +
                '   <th>加载次数</th>' +
                '   <th>创建时间</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.getDataList.length; i++) {
                    
                    // 单行数据对象
                    // 用于编辑、查看、分享时的参数传递
                    var dataInfoObject = {
                        data_id: res.getDataList[i].data_id,
                        data_title: res.getDataList[i].data_title,
                        data_jumplink: res.getDataList[i].data_jumplink,
                        data_dxccym: res.getDataList[i].data_dxccym
                    };
                    
                    // 状态切换
                    if(res.getDataList[i].data_status == 1){
                        
                        // 正常
                        var data_status = 
                        '<span class="switch-on" id="'+res.getDataList[i].data_id+'" onclick="changeDataStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }else{
                        
                        // 关闭
                        var data_status = 
                        '<span class="switch-off" id="'+res.getDataList[i].data_id+'" onclick="changeDataStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.getDataList[i].data_id+'</td>' +
                        '   <td>'+res.getDataList[i].data_title+'</td>' +
                        '   <td>'+res.getDataList[i].data_jumplink+'</td>' +
                        '   <td>'+res.getDataList[i].data_pv+'</td>' +
                        '   <td>'+res.getDataList[i].data_create_time+'</td>' +
                        '   <td>'+data_status+'</td>' +
                        '   <td style="text-align:right;">' +
                        '       <span data-toggle="modal" data-target="#shareDataModal" onclick="shareData('+res.getDataList[i].data_id+')" class="cz-click">分享</span>' +
                        '       <span data-toggle="modal" data-target="#editMultiJumpLinkModal" class="cz-click" onclick=\'getDataInfo('+JSON.stringify(dataInfoObject)+')\'>编辑</span>' +
                        '       <span data-toggle="modal" data-target="#delDataModal" onclick="delDataConfirmModal('+res.getDataList[i].data_id+')" class="cz-click">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
                
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../../../login/');
                }
                
                // 非200状态码
                noData(res.msg);
            }
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getDataList.php');
      },
    });
}

// 分页组件
function fenyeComponent(thisPage,allPage,nextPage,prePage){
    
    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1 且 总页码=1
        // 无需显示分页控件
        $("#right .data-card .fenye").css("display","none");
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1 且 总页码>1
        // 代表还有下一页
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">'+ 
        '           <img src="../../../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        $("#right .data-card .fenye").width("80px");
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '   <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '       <img src="../../../../static/img/prevPage.png" />'+ 
        '   </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        $("#right .data-card .fenye").width("80px");
    }else{
        
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '           <img src="../../../../static/img/prevPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">'+ 
        '           <img src="../../../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        $("#right .data-card .fenye").width("150px");
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($fenyeComponent_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getDataList(pageNum);
}

// 创建数据
function createData(){
    
    $.ajax({
        type: "POST",
        url: "server/createData.php",
        data: $('#createData').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createDataModal")', 500);
                
                // 重新加载列表
                setTimeout('getDataList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                if(res.code == 101) {
                    
                    showErrorResult(res.msg+'<a href="'+res.buy_link+'" target="_blank">'+res.buy_link+'</a>')
                }else {
                    
                    showErrorResult(res.msg)
                }
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createData.php');
        }
    });
}

// 删除确认
function delDataConfirmModal(data_id){
    
    // 将 data_id 添加到确认按钮
    $('#delDataModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delData('+data_id+');">确认删除</button>'
    )
}

// 执行删除
function delData(data_id){

    $.ajax({
        type: "GET",
        url: "server/delData.php?data_id=" + data_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delDataModal");
                
                // 重新加载列表
                setTimeout('getDataList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delData.php');
        }
    });
}

// 获取详情
function getDataInfo(dataInfoObject){

    // 获取域名列表
    getDomainNameList('edit');
    
    // 当前设置的域名
    setTimeout(function() {
        $("#editDataModal select[name='data_dxccym']").val(dataInfoObject.data_dxccym);
    },100)
    
    // 填充表单数据
    $("#editDataModal input[name='data_title']").val(dataInfoObject.data_title);
    $("#editDataModal input[name='data_jumplink']").val(dataInfoObject.data_jumplink);
    $("#editDataModal input[name='data_id']").val(dataInfoObject.data_id);
    
    // 显示Modal
    showModal('editDataModal');
}

// 编辑
function editData(){
    
    $.ajax({
        type: "POST",
        url: "server/editData.php",
        data: $('#editData').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editDataModal")', 500);
                
                // 重新加载列表
                setTimeout('getDataList();', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editData.php');
        }
    });
}

// 分享
function shareData(data_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "server/shareData.php?data_id="+data_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 链接
                $("#shareUrl").html('<span id="data_'+data_id+'">' + res.shareUrl + '</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#shareDataModal .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#data_'+data_id+'">复制链接</button>'
                );
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareData.php');
        }
    });
}

// 切换状态
function changeDataStatus(e) {
    
    $.ajax({
        type: "POST",
        url: "server/changeDataStatus.php?data_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                getDataList();
                showNotification(res.msg)
            }else{
                
                // 操作失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误');
        }
    });
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
                location.href = '../../../login/';
            }
        },
        error: function() {
            
            // 服务器发生错误
            errorPage('data-list','exitLogin.php');
        }
    });
}

// 使用 appendOptionsToSelect函数来为每个select元素处理选项的添加
function appendOptionsToSelect(selectElement, dataList) {
    
    if (dataList.length > 0) {
        
        // 有域名
        for (var i = 0; i < dataList.length; i++) {
            
            // 添加至指定的节点
            selectElement.append(
                '<option value="' + dataList[i].domain + '">' + dataList[i].domain + '</option>'
            );
        }
    } else {
        
        // 暂无域名
        selectElement.append('<option value="">暂无域名</option>');
    }
}

// 获取域名列表
function getDomainNameList(module){
    
    // 初始化
    initialize_getDomainNameList(module);

    // 获取
    $.ajax({
        type: "GET",
        url: "../../../public/getDomainNameList.php",
        success: function (res) {
            
            // 成功
            if (res.code == 200) {
                
                // 创建
                appendOptionsToSelect($("#createDataModal select[name='data_dxccym']"), res.yccymList);
                
                // 编辑
                appendOptionsToSelect($("#editDataModal select[name='data_dxccym']"), res.yccymList);
            } else {
                
                // 操作失败
                showErrorResult(res.msg);
            }
        },
        error: function () {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！');
        }
    });
}

// 设置路由
function setRouter(pageNum){
    
    // 当前页码不等于1的时候
    if(pageNum !== 1){
        window.history.pushState('', '', '?p='+pageNum);
    }
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

// 排查提示1
function showErrorResultForphpfileName(phpfileName){
    $('#app .result').html('<div class="error">服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+phpfileName+'的返回信息进行排查！<a href="../../../../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a></div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 3000);
}

// 排查提示2
function errorPage(from,text){
    
    if(from == 'data-list'){
        
        $("#right .data-list").css('display','none');
        $("#right .data-card .loading").html(
            '<img src="../../../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
        $("#right .data-card .loading").css('display','block');
    }else if(from == 'data-card'){
        
        $("#right .data-card").html(
            '<img src="../../../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }else if(from == 'qrcode-list'){

        $("#qunQrcodeListModal table").html(
            '<img src="../../../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }else if(from == 'bingliuList'){

        $("#bingliuModal .bingliuList").html(
            '<img src="../../../../static/img/errorIcon.png" class="errorIMG" /><br/>' +
            '<p class="errorTEXT">服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../../../static/img/tiaoshi.jpg" target="blank" class="errorA">点击查看排查方法</a>'
        );
    }
    
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

// 初始化（getDataList获取中间页列表）
function initialize_getDataList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        
        // 创建时的表单初始化
        $('#createDataModal input[name="data_title"]').val('');
        $('#createDataModal input[name="data_jumplink"]').val('');
        
        // 域名初始化
        $('#createDataModal select[name="data_dxccym"]').empty();
        
        // 隐藏提示
        hideResult();

    }else if(module == 'edit'){
        
        // 域名初始化
        $('#editDataModal select[name="data_dxccym"]').empty();
        hideResult();
    }
}

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
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