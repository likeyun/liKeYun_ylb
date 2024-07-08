
// 打开网页就是从这里开始执行代码
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取安装状态
    getSetupStatu();
    
    // clipboard插件
    var clipboard = new ClipboardJS('#ShareJwModal .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#ShareJwModal .modal-footer button').text('已复制');
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
                    getJwList(pageNum);
                }else{
                    
                    // 获取首页
                    getJwList();
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
function getJwList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "server/getJwList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "server/getJwList.php?p="+pageNum
        
        // 设置URL路由
        setRouter(pageNum);
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getJwList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>Jwid</th>' +
                '   <th>图标</th>' +
                '   <th>标题</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问次数</th>' +
                '   <th>点击次数</th>' +
                '   <th>备注</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.jwList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // 标题
                    var jw_title = res.jwList[i].jw_title;
                    
                    // 图标
                    var jw_icon = res.jwList[i].jw_icon;
                    
                    // 创建时间
                    var jw_create_time = res.jwList[i].jw_create_time;
                    
                    // 访问次数
                    var jw_pv = res.jwList[i].jw_pv;
                    
                    // 点击次数
                    var jw_clickNum = res.jwList[i].jw_clickNum;
                    
                    var jw_beizhu = res.jwList[i].jw_beizhu;
                    
                    if(jw_beizhu) {
                        
                        var jw_beizhu_text = jw_beizhu;
                    }else {
                        var jw_beizhu_text = '无备注';
                    }
                    
                    // ID
                    var jw_id = res.jwList[i].jw_id;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+res.jwList[i].jw_id+'</td>' +
                        '   <td><img src="'+ jw_icon +'" width="35" /></td>' +
                        '   <td>'+jw_title+'</td>' +
                        '   <td>'+jw_create_time+'</td>' +
                        '   <td>'+jw_pv+'</td>' +
                        '   <td>'+jw_clickNum+'</td>' +
                        '   <td>'+jw_beizhu_text+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#ShareJwModal" onclick="shareJw('+jw_id+')">分享</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editJwModal" onclick="getJwInfo('+jw_id+')">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#delJwModal" onclick="askDelJw('+jw_id+')">删除</span>' +
                        '           </div>' +
                        '       </div>' +
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
    getJwList(pageNum);
}

// 上传文件（创建）
document.addEventListener('DOMContentLoaded', function() {
    
    // 选择素材
    $("#up1").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("createJw"));
            
            // 上传图标
            uploadIcon(imageData);
        }
        
    });
    
    // 选择素材
    $("#up2").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("createJw"));
            
            // 上传图标
            uploadbgimg(imageData);
        }
        
    });
    
    // 上传图标
    function uploadIcon(imageData){

        $.ajax({
            url: "server/up1.php",
            type: "POST",
            data: imageData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(res) {
                
                if(res.code == 200){
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_1").text('重新上传');
                    
                    // 设置表单Url
                    $('#createJwModal input[name="jw_icon"]').val(res.url);
                }else{
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_1").text('上传失败');
                }
            },
            error: function() {
                
                // 上传失败
                $("#chooseIMG_1").text('上传失败');
            },
            beforeSend: function(res){
                
                // 修改上传按钮文字
                $("#chooseIMG_1").text('上传中...');
            }
        });
    }
    
    // 上传背景
    function uploadbgimg(imageData){

        $.ajax({
            url: "server/up2.php",
            type: "POST",
            data: imageData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(res) {
                
                if(res.code == 200){
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_2").text('重新上传');
                    
                    // 设置表单Url
                    $('#createJwModal input[name="jw_bgimg"]').val(res.url);
                }else{
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_2").text('上传失败');
                }
            },
            error: function() {
                
                // 上传失败
                $("#chooseIMG_2").text('上传失败');
            },
            beforeSend: function(res){
                
                // 修改上传按钮文字
                $("#chooseIMG_2").text('上传中...');
            }
        });
    }
    
    $("#up1").val('');
    $("#up2").val('');
    $("#createJwModal input[name='file1']").val('');
    $("#createJwModal input[name='file2']").val('');
})

// 上传文件（编辑）
document.addEventListener('DOMContentLoaded', function() {
    
    // 选择素材
    $("#up3").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("editJw"));
            
            // 上传图标
            uploadIcon(imageData);
        }
        
    });
    
    // 选择素材
    $("#up4").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("editJw"));
            
            // 上传图标
            uploadbgimg(imageData);
        }
        
    });
    
    // 上传图标
    function uploadIcon(imageData){

        $.ajax({
            url: "server/up1.php",
            type: "POST",
            data: imageData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(res) {
                
                if(res.code == 200){
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_3").text('重新上传');
                    
                    // 设置表单Url
                    $('#editJwModal input[name="jw_icon"]').val(res.url);
                }else{
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_3").text('上传失败');
                }
            },
            error: function() {
                
                // 上传失败
                $("#chooseIMG_3").text('上传失败');
            },
            beforeSend: function(res){
                
                // 修改上传按钮文字
                $("#chooseIMG_3").text('上传中...');
            }
        });
    }
    
    // 上传背景
    function uploadbgimg(imageData){

        $.ajax({
            url: "server/up2.php",
            type: "POST",
            data: imageData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(res) {
                
                if(res.code == 200){
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_4").text('重新上传');
                    
                    // 设置表单Url
                    $('#editJwModal input[name="jw_bgimg"]').val(res.url);
                }else{
                    
                    // 修改上传按钮文字
                    $("#chooseIMG_4").text('上传失败');
                }
            },
            error: function() {
                
                // 上传失败
                $("#chooseIMG_4").text('上传失败');
            },
            beforeSend: function(res){
                
                // 修改上传按钮文字
                $("#chooseIMG_4").text('上传中...');
            }
        });
    }
    
    $("#up3").val('');
    $("#up4").val('');
    $("#editJwModal input[name='file1']").val('');
    $("#editJwModal input[name='file2']").val('');
})

// 创建链接
function createJw(){
    
    $.ajax({
        type: "POST",
        url: "server/createJw.php",
        data: $('#createJw').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createJwModal")', 500);
                
                // 重新加载列表
                setTimeout('getJwList();', 500);
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
            showErrorResultForphpfileName('createJw.php');
        }
    });
}

// 询问是否要删除
function askDelJw(jwid){
    
    // 将群id添加到button的
    // delJw函数用于传参执行删除
    $('#delJwModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delJw('+jwid+');">确定删除</button>'
    )
}

// 删除
function delJw(jwid){

    $.ajax({
        type: "GET",
        url: "server/delJw.php?jwid=" + jwid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delJwModal");
                
                // 重新加载列表
                setTimeout('getJwList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delJw.php');
        }
    });
}

// 获取链接详情
function getJwInfo(jw_id){
    
    $.ajax({
        type: "GET",
        url: "server/getJwInfo.php?jw_id="+jw_id,
        success: function(res){

            if(res.code == 200){
                
                // 标题
                $('#editJwModal input[name="jw_title"]').val(res.jwInfo.jw_title);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // 获取当前设置的域名
                $('#editJwModal select[name="jw_dxccym"]').append(
                    '<option value="'+res.jwInfo.jw_dxccym+'">'+res.jwInfo.jw_dxccym+'</option>'
                );
                
                // 图标
                $('#editJwModal input[name="jw_icon"]').val(res.jwInfo.jw_icon);
                
                // 背景图片
                $('#editJwModal input[name="jw_bgimg"]').val(res.jwInfo.jw_bgimg);
                
                // 备注
                $('#editJwModal input[name="jw_beizhu"]').val(res.jwInfo.jw_beizhu);
                
                // 目标链接
                $('#editJwModal input[name="jw_url"]').val(res.jwInfo.jw_url);
                
                // ID
                $('#editJwModal input[name="jw_id"]').val(res.jwInfo.jw_id);
                            
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getJwInfo.php');
        }
    });
}

// 编辑链接
function editJw(){
    
    $.ajax({
        type: "POST",
        url: "server/editJw.php",
        data: $('#editJw').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editJwModal")', 500);
                
                // 重新加载列表
                setTimeout('getJwList();', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editJw.php');
        }
    });
}

// 分享链接
function shareJw(jwid){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "server/shareJw.php?jw_id="+jwid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").html('<span id="jw_'+jwid+'">' + res.longUrl + '</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#ShareJwModal .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#jw_'+jwid+'">复制链接</button>'
                );
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareJw.php');
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
                appendOptionsToSelect($("#createJwModal select[name='jw_dxccym']"), res.yccymList);
                
                // 编辑
                appendOptionsToSelect($("#editJwModal select[name='jw_dxccym']"), res.yccymList);
                
                // 获取本地的背景图Url
                const jw_bgimg_gif = window.location.href + 'img/jw_bgimg.gif';
                
                // 设置到表单中
                $('#createJwModal input[name="jw_bgimg"]').val(jw_bgimg_gif);
                
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

// 初始化（getJwList获取中间页列表）
function initialize_getJwList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        $('#createJwModal input[name="jw_title"]').val('');
        $('#createJwModal input[name="jw_icon"]').val('');
        $('#createJwModal input[name="jw_bgimg"]').val('');
        $('#createJwModal input[name="jw_beizhu"]').val('');
        $('#createJwModal input[name="jw_urlscheme"]').val('');
        $('#createJwModal input[name="jw_caoliaoqrcode"]').val('');
        $('#createJwModal input[name="jw_jinshandoc"]').val('');
        $('#createJwModal input[name="jw_tencentdoc"]').val('');
        $('#createJwModal input[name="jw_workwxpan"]').val('');
        $("#chooseIMG_1").text('上传图片');
        $("#chooseIMG_2").text('上传图片');
        $('#createJwModal select[name="jw_dxccym"]').empty();
        hideResult();

    }else if(module == 'edit'){
        $('#editJwModal select[name="jw_dxccym"]').empty();
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