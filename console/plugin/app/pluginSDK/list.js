// 打开网页就是从这里开始执行代码
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取安装状态
    getSetupStatu();
    
    // 获取当前页面id
    data_id = queryURLParams(window.location.href).data_id;
    if(!data_id){
        
        // 无参数的时候显示
        $('#right .data-card').html('<p style="text-align:center;padding:20px 0 0;">参数缺失</p>');
    }
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
                
                // 获取参数
                var pageNum = queryURLParams(window.location.href).p;

                if(pageNum !== 'undefined'){
                    
                    // 获取当前页码数据列表
                    getLists(pageNum,data_id);
                }else{
                    
                    // 获取首页
                    getLists(1,data_id);
                }
            }
        },
        error: function() {
            
            // 服务器发生错误
            noData('getSetupStatu.php服务器发生错误');
        }
    });
}

// 获取列表
function getLists(pageNum,data_id) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "server/list/getLists.php?data_id="+data_id;
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "server/list/getLists.php?p="+pageNum+"&data_id="+data_id
        
        // 设置URL路由
        setRouter(pageNum,data_id);
    }
    
    // 将 data_id 设置到添加数据的表单中
    $('#addListDataModal input[name="data_id"]').val(data_id);
    
    // 将 data_id 设置到清空所有数据的按钮中
    $("#delall").attr("onclick", "delAllListDataModal("+data_id+")");

    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getLists();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>ID</th>' +
                '   <th>字段1</th>' +
                '   <th>字段2</th>' +
                '   <th>字段3</th>' +
                '   <th>字段4</th>' +
                '   <th>状态</th>' +
                '   <th>时间</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                $("#delall").css('display','block');
                
                // 遍历数据
                let listdata_status;
                for (var i=0; i<res.getLists.length; i++) {
                    
                    if(res.getLists[i].listdata_status == 1) {
                        
                        listdata_status = '<span style="color:#22ac38;">正常</span>';
                    }else {
                        
                        listdata_status = '<span style="color:#f00;">关闭</span>';
                    }
                    
                    // 每一行的数据对象
                    var currentLineData = {
                        data_id: res.getLists[i].data_id,
                        listdata_id: res.getLists[i].listdata_id,
                        listdata_1: res.getLists[i].listdata_1,
                        listdata_2: res.getLists[i].listdata_2,
                        listdata_3: res.getLists[i].listdata_3,
                        listdata_4: res.getLists[i].listdata_4,
                        listdata_status: res.getLists[i].listdata_status
                    };

                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.getLists[i].listdata_id+'</td>' +
                        '   <td>'+res.getLists[i].listdata_1+'</td>' +
                        '   <td>'+res.getLists[i].listdata_2+'</td>' +
                        '   <td>'+res.getLists[i].listdata_3+'</td>' +
                        '   <td>'+res.getLists[i].listdata_4+'</td>' +
                        '   <td>'+listdata_status+'</td>' +
                        '   <td>'+res.getLists[i].listdata_addtime+'</td>' +
                        '   <td style="white-space: nowrap;">' +
                        '       <span class="cz-click" onclick=\'getListDataInfo('+JSON.stringify(currentLineData)+')\'>编辑</span>' +
                        '       <span class="cz-click" onclick="delListDataModal('+res.getLists[i].listdata_id+')">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
                
            }else{
                
                $("#delall").css('display','none');
                
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
        errorPage('data-list','getLists.php');
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
        $(".fenye").css("width","80px");
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
        $(".fenye").css("width","80px");
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
        $(".fenye").css("width","150px");
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($fenyeComponent_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getLists(pageNum,data_id);
}

// 添加数据
function addListData(){
    
    $.ajax({
        type: "POST",
        url: "server/list/addListData.php",
        data: $('#addListData').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈
                showSuccessResult(res.msg)
                
                // 隐藏 Modal
                setTimeout('hideModal("addListDataModal")', 500);
                
                // 重新加载列表
                setTimeout('getLists(1,'+data_id+');', 600);
                
                // 清空表单
                setTimeout(function(){
                    $('#addListDataModal textarea').val('');
                },700)
            }else{
                
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('addListData.php');
        }
    });
}

// 获取当前行的数据
function getListDataInfo(dataObject) {

    // 将获取到的数据填充到编辑的表单中
    $("#editListDataModal input[name='listdata_id']").val(dataObject.listdata_id);
    $("#editListDataModal input[name='data_id']").val(dataObject.data_id);
    $("#editListDataModal input[name='listdata_1']").val(dataObject.listdata_1);
    $("#editListDataModal input[name='listdata_2']").val(dataObject.listdata_2);
    $("#editListDataModal textarea[name='listdata_3']").val(dataObject.listdata_3);
    $("#editListDataModal select[name='listdata_4']").val(dataObject.listdata_4);
    $("#editListDataModal select[name='listdata_status']").val(dataObject.listdata_status);
    
    // 显示Modal
    showModal('editListDataModal');
}

// 删除确认
function delListDataModal(listdata_id){
    
    // 给按钮添加参数
    $('#delListDataModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delListData('+listdata_id+');">确认删除</button>'
    )
    
    // 显示Modal
    showModal('delListDataModal');
}

// 执行删除
function delListData(listdata_id){

    $.ajax({
        type: "GET",
        url: "server/list/delListData.php?listdata_id=" + listdata_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delListDataModal");
                
                // 重新加载列表
                setTimeout('getLists(1,'+data_id+')', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delListData.php');
        }
    });
}

// 清空所有数据
function delAllListDataModal(data_id){
    
    // 给按钮添加参数
    $('#delAllListDataModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delAllListData('+data_id+');">确认清空</button>'
    )
}

// 执行清空
function delAllListData(data_id){

    $.ajax({
        type: "GET",
        url: "server/list/delAllListData.php?data_id=" + data_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delAllListDataModal");
                
                // 重新加载列表
                setTimeout('getLists(1,'+data_id+')', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
                setTimeout('$(".fenye").remove()', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delAllListData.php');
        }
    });
}

// 提交编辑
function editListData(){
    
    $.ajax({
        type: "POST",
        url: "server/list/editListData.php",
        data: $('#editListData').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editListDataModal")', 500);
                
                // 重新加载列表
                setTimeout('getLists(1,'+data_id+');', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editListData.php');
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

// 设置路由
function setRouter(pageNum,data_id){
    
    // 当前页码不等于1的时候
    if(pageNum !== 1){
        window.history.pushState('', '', '?p='+pageNum+'&data_id='+data_id);
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

function initialize_getLists(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
    
    // 初始化表单
    $('#addListDataModal input[name="listdata_1"]').val('');
    $('#addListDataModal input[name="listdata_2"]').val('');
    $('#addListDataModal input[name="listdata_4"]').val('');
    $('#addListDataModal textarea').val('');
    hideResult();
    
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        
        // 创建时的表单初始化
        $('#createDataModal input[name="data_title"]').val('');
        $('#createDataModal input[name="data_pic"]').val('');
        $('#createDataModal input[name="data_jumplink"]').val('');
        
        // 域名初始化
        $('#createDataModal select[name="data_dlym"]').empty();
        $('#createDataModal select[name="data_rkym"]').empty();
        $('#createDataModal select[name="data_ldym"]').empty();
        
        // 隐藏提示
        hideResult();

    }else if(module == 'edit'){
        
        // 域名初始化
        $('#editDataModal select[name="data_dlym"]').empty();
        $('#editDataModal select[name="data_rkym"]').empty();
        $('#editDataModal select[name="data_ldym"]').empty();
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