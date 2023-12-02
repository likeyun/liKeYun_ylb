
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码数据列表
        getZjyList(pageNum);
    }else{
        
        // 获取首页
        getZjyList();
    }
    
    // clipboard插件
    var clipboard = new ClipboardJS('#ShareZjyModal .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#ShareZjyModal .modal-footer button').text('已复制');
    });
    
    // 监听multi_project可编辑的DIV的输入
    $("#createSpaModal .multi_project").on("input", function() {
        
        // 实时获取输入的内容
        const multi_project_content = $(this).val();
        
        // 将内容实时加入到表单输入框
        $('#createSpaModal input[name="multiSPA_project"]').val(multi_project_content.replace(/\n/g, "<br/>"));
    });
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
                // 账号信息
                var $accountInfo_HTML = $(
                    '<span class="user_name">'+res.user_name+'</span>' +
                    '<span onclick="exitLogin();">退出</span>'
                );
                $("#accountInfo").html($accountInfo_HTML);
                
                // 初始化
                initialize_Login('login',res.user_admin)
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
                
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

// 登录后的一些初始化
function initialize_Login(loginStatus,adminStatus){
    
    if(loginStatus == 'login'){
        
        // 显示创建按钮
        $('#button-view').css('display','block');
        
        // 判断管理员权限
        if(adminStatus == 1){
            
            // 显示开放API按钮
            $('#openApi').html(
                '<a href="./openApi.html"><button class="tint-btn" style="margin-left: 5px;">开放API</button></a>'
            );
        }
        
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
        $('#openApi').css('display','none');
    }
}

// 获取中间页列表
function getZjyList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getZjyList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getZjyList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getZjyList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>标题</th>' +
                '   <th>淘口令</th>' +
                '   <th>原价</th>' +
                '   <th>券后价</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问次数</th>' +
                '   <th>复制次数</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.zjyList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）标题
                    var zjy_title = res.zjyList[i].zjy_short_title;
                    
                    // （3）淘口令
                    var zjy_tkl = res.zjyList[i].zjy_tkl;
                    
                    // （4）原价
                    var zjy_original_cost = res.zjyList[i].zjy_original_cost;
                    
                    // （5）券后价
                    var zjy_discounted_price = res.zjyList[i].zjy_discounted_price;
                    
                    // （6）创建时间
                    var zjy_create_time = res.zjyList[i].zjy_create_time;
                    
                    // （7）访问次数
                    var zjy_pv = res.zjyList[i].zjy_pv;
                    
                    // （8）复制次数
                    var zjy_copyNum = res.zjyList[i].zjy_copyNum;
                    
                    // （9）ID
                    var zjy_id = res.zjyList[i].zjy_id;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+zjy_title+'</td>' +
                        '   <td>'+zjy_tkl+'</td>' +
                        '   <td>'+zjy_original_cost+'</td>' +
                        '   <td>'+zjy_discounted_price+'</td>' +
                        '   <td>'+zjy_create_time+'</td>' +
                        '   <td>'+zjy_pv+'</td>' +
                        '   <td>'+zjy_copyNum+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#ShareZjyModal" onclick="shareZjy('+zjy_id+')">分享</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#EditZjyModal" onclick="getZjyInfo('+zjy_id+')">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#DelZjyModal" onclick="askDelZjy('+zjy_id+')">删除</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
                
                // 设置URL路由
                setRouter(pageNum);
                
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 非200状态码
                noData(res.msg);
                
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getZjyList.php');
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
        '           <img src="../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '   <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '       <img src="../../static/img/prevPage.png" />'+ 
        '   </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else{
        
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '           <img src="../../static/img/prevPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">'+ 
        '           <img src="../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($fenyeComponent_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getZjyList(pageNum);
}

// 创建中间页
function createZjy(){
    
    $.ajax({
        type: "POST",
        url: "./createZjy.php",
        data: $('#createZjy').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createZjyModal")', 500);
                
                // 重新加载中间页列表
                setTimeout('getZjyList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createZjy.php');
        }
    });
}

// 编辑中间页
function editZjy(){
    
    $.ajax({
        type: "POST",
        url: "./editZjy.php",
        data: $('#editZjy').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("EditZjyModal")', 500);
                
                // 重新加载中间页列表
                setTimeout('getZjyList();', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editZjy.php');
        }
    });
}

// 询问是否要删除中间页
function askDelZjy(zjyid){
    
    // 将群id添加到button的
    // delZjy函数用于传参执行删除
    $('#DelZjyModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delZjy('+zjyid+');">确定删除</button>'
    )
}

// 删除中间页
function delZjy(zjyid){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delZjy.php?zjyid="+zjyid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("DelZjyModal");
                
                // 重新加载中间页列表
                setTimeout('getZjyList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delZjy.php');
        }
    });
}

// 获取中间页详情
function getZjyInfo(zjy_id){
    
    $.ajax({
        type: "GET",
        url: "./getZjyInfo.php?zjy_id="+zjy_id,
        success: function(res){

            if(res.code == 200){
                
                // （1）长标题
                $('#zjy_long_title_edit').val(res.zjyInfo.zjy_long_title);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // （2）获取当前设置的域名
                $("#zjy_rkym_edit").append(
                    '<option value="'+res.zjyInfo.zjy_rkym+'">'+res.zjyInfo.zjy_rkym+'</option>'
                );
                
                $("#zjy_ldym_edit").append(
                    '<option value="'+res.zjyInfo.zjy_ldym+'">'+res.zjyInfo.zjy_ldym+'</option>'
                );
                
                $("#zjy_dlym_edit").append(
                    '<option value="'+res.zjyInfo.zjy_dlym+'">'+res.zjyInfo.zjy_dlym+'</option>'
                );
                
                // （3）短标题
                $('#zjy_short_title_edit').val(res.zjyInfo.zjy_short_title);
                
                // （4）淘口令
                $('#zjy_tkl_edit').val(res.zjyInfo.zjy_tkl);
                
                // （5）原价
                $('#zjy_original_cost_edit').val(res.zjyInfo.zjy_original_cost);
                
                // （6）券后价
                $('#zjy_discounted_price_edit').val(res.zjyInfo.zjy_discounted_price);
                
                // （7）商品主图
                $('#zjy_goods_img_edit').val(res.zjyInfo.zjy_goods_img);
                
                // （8）商品链接
                $('#zjy_goods_link_edit').val(res.zjyInfo.zjy_goods_link);
                
                // （8）zjy_id
                $('#zjy_id_edit').val(zjy_id);
                            
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getZjyInfo.php');
        }
    });
}

// 获取中间页配置
function getZjyConfig(){
    
    // 初始化
    hideResult();
    
    // 获取
    $.ajax({
        type: "GET",
        url: "./getZjyConfig.php",
        success: function(res){

            if(res.code == 200){
                
                // （1）zjy_config_appkey
                $('#zjy_config_appkey').val(res.zjyConfigInfo.zjy_config_appkey);
                
                // （2）zjy_config_sid
                $('#zjy_config_sid').val(res.zjyConfigInfo.zjy_config_sid);
                
                // （3）zjy_config_pid
                $('#zjy_config_pid').val(res.zjyConfigInfo.zjy_config_pid);
                
                // （4）zjy_config_tbname
                $('#zjy_config_tbname').val(res.zjyConfigInfo.zjy_config_tbname);
                            
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getZjyConfig.php');
        }
    });
}

// 提交配置
function configZjy(){
    
    $.ajax({
        type: "POST",
        url: "./configZjy.php",
        data: $('#configZjy').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("configZjyModal")', 500);

            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('configZjy.php');
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
        url: "../public/getDomainNameList.php",
        success: function (res) {
            
            // 成功
            if (res.code == 200) {
                
                // 创建
                appendOptionsToSelect($("#createZjyModal select[name='zjy_rkym']"), res.rkymList);
                appendOptionsToSelect($("#createZjyModal select[name='zjy_ldym']"), res.ldymList);
                appendOptionsToSelect($("#createZjyModal select[name='zjy_dlym']"), res.dlymList);
                
                // 编辑
                appendOptionsToSelect($("#EditZjyModal select[name='zjy_rkym']"), res.rkymList);
                appendOptionsToSelect($("#EditZjyModal select[name='zjy_ldym']"), res.ldymList);
                appendOptionsToSelect($("#EditZjyModal select[name='zjy_dlym']"), res.dlymList);
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

// 分享中间页
function shareZjy(zjy_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    // 分享
    $.ajax({
        type: "GET",
        url: "./shareZjy.php?zjy_id="+zjy_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").text(res.longUrl);
                
                // 短链接
                $("#shortUrl").html('<span id="zjy_'+zjy_id+'">'+res.shortUrl+'</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#ShareZjyModal .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#zjy_'+zjy_id+'">复制链接</button>'
                );
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareZjy.php');
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

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
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

// 设置URL路由
function setRouter(pageNum){
    
    // 第一页不设置
    if(pageNum !== 1){
        
        // 根据页码+token设置路由
        window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
    }
}

// 隐藏全局信息提示弹出提示
function hideNotification() {
	var $notificationContainer = $('#notification');
	$notificationContainer.css('top', '-100px');
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

// 暂无数据
function noData(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noData.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化（getZjyList获取中间页列表）
function initialize_getZjyList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        $('#createZjyModal input[name="taokouling"]').val('');
        $('#createZjyModal input[name="zjy_long_title"]').val('');
        $('#createZjyModal input[name="zjy_short_title"]').val('');
        $('#createZjyModal input[name="zjy_tkl"]').val('');
        $('#createZjyModal input[name="zjy_original_cost"]').val('');
        $('#createZjyModal input[name="zjy_discounted_price"]').val('');
        $('#createZjyModal input[name="zjy_goods_img"]').val('');
        $("#selectGoodsImgtext").text('上传图片');
        $('#createZjyModal select[name="zjy_rkym"]').empty();
        $('#createZjyModal select[name="zjy_ldym"]').empty();
        $('#createZjyModal select[name="zjy_dlym"]').empty();
        hideResult();

    }else if(module == 'edit'){
        $('#EditZjyModal select[name="zjy_rkym"]').empty();
        $('#EditZjyModal select[name="zjy_ldym"]').empty();
        $('#EditZjyModal select[name="zjy_dlym"]').empty();
        hideResult();
    }
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