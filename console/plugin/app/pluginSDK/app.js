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
    
    // 时间选择器默认值
    function getOneMonthLater() {
        let now = new Date();
        now.setMonth(now.getMonth() + 1); // 增加1个月
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // 处理时区偏移
    
        return now.toISOString().slice(0, 16); // 格式化 YYYY-MM-DDTHH:MM
    }
    document.getElementById('data_expire_time_picker').value = getOneMonthLater();
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
                '   <th>访问限制</th>' +
                '   <th>访问次数</th>' +
                '   <th>创建时间</th>' +
                '   <th>到期时间</th>' +
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
                                        
                    // 访问限制
                    if(res.getDataList[i].data_limit == 1) {
                        var data_limit_text = '不限制';
                    }else if(res.getDataList[i].data_limit == 2) {
                        var data_limit_text = '只允许在手机打开';
                    }else if(res.getDataList[i].data_limit == 3) {
                        var data_limit_text = '只允许在微信内打开';
                    }else if(res.getDataList[i].data_limit == 4) {
                        var data_limit_text = '只允许在QQ内打开';
                    }else if(res.getDataList[i].data_limit == 5) {
                        var data_limit_text = '只允许在抖音内打开';
                    }
                    
                    // 单行数据对象
                    // 用于编辑、查看、分享时的参数传递
                    var dataInfoObject = {
                        data_id: res.getDataList[i].data_id,
                        data_title: res.getDataList[i].data_title,
                        data_pic: res.getDataList[i].data_pic,
                        data_limit: res.getDataList[i].data_limit,
                        data_jumplink: res.getDataList[i].data_jumplink,
                        data_expire_time: res.getDataList[i].data_expire_time,
                        data_dlym: res.getDataList[i].data_dlym,
                        data_rkym: res.getDataList[i].data_rkym,
                        data_ldym: res.getDataList[i].data_ldym,
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
                    
                    // 格式化到期时间
                    function formatDateTime(datetimeLocal) {
                        let date = new Date(datetimeLocal);
                        let year = date.getFullYear();
                        let month = String(date.getMonth() + 1).padStart(2, "0"); // 月份从 0 开始
                        let day = String(date.getDate()).padStart(2, "0");
                        let hours = String(date.getHours()).padStart(2, "0");
                        let minutes = String(date.getMinutes()).padStart(2, "0");
                        return `${year}-${month}-${day} ${hours}:${minutes}`;
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.getDataList[i].data_id+'</td>' +
                        '   <td>'+res.getDataList[i].data_title+'</td>' +
                        '   <td>'+data_limit_text+'</td>' +
                        '   <td>'+res.getDataList[i].data_pv+'</td>' +
                        '   <td>'+res.getDataList[i].data_create_time+'</td>' +
                        '   <td>'+formatDateTime(res.getDataList[i].data_expire_time)+'</td>' +
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

// 获取数据详情
function getDataInfo(dataInfoObject){

    // 获取域名列表
    getDomainNameList('edit');
    
    // 当前设置的域名
    setTimeout(function() {
        $("#editDataModal select[name='data_dlym']").val(dataInfoObject.data_dlym);
        $("#editDataModal select[name='data_rkym']").val(dataInfoObject.data_rkym);
        $("#editDataModal select[name='data_ldym']").val(dataInfoObject.data_ldym);
    },100)
    
    // 填充表单数据
    $("#editDataModal input[name='data_title']").val(dataInfoObject.data_title);
    $("#editDataModal select[name='data_limit']").val(dataInfoObject.data_limit);
    $("#editDataModal input[name='data_expire_time']").val(dataInfoObject.data_expire_time);
    $("#editDataModal input[name='data_pic']").val(dataInfoObject.data_pic);
    $("#editDataModal input[name='data_jumplink']").val(dataInfoObject.data_jumplink);
    $("#editDataModal input[name='data_id']").val(dataInfoObject.data_id);
    
    // 显示Modal
    showModal('editDataModal');
}

// 编辑数据
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

// 分享数据
function shareData(data_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "server/shareData.php?data_id="+data_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 短链接
                $("#shortUrl").html('<span id="data_'+data_id+'">' + res.shortUrl + '</span>');
                
                // 长链接
                $("#longUrl").html('<span>' + res.longUrl + '</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#shareDataModal .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#data_'+data_id+'">复制短链接</button>'
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
                appendOptionsToSelect($("#createDataModal select[name='data_dlym']"), res.dlymList);
                appendOptionsToSelect($("#createDataModal select[name='data_rkym']"), res.rkymList);
                appendOptionsToSelect($("#createDataModal select[name='data_ldym']"), res.ldymList);
                
                // 编辑
                appendOptionsToSelect($("#editDataModal select[name='data_dlym']"), res.dlymList);
                appendOptionsToSelect($("#editDataModal select[name='data_rkym']"), res.rkymList);
                appendOptionsToSelect($("#editDataModal select[name='data_ldym']"), res.ldymList);
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

// 获取素材
function getSuCai(pageNum,fromPannel,fromINput){
    
    // 初始化
    $('#suCaiKu .modal-body .sucai-view').empty('');
    
    // 关闭创建界面
    hideModal('createDataModal');
    
    // 关闭编辑界面
    hideModal('editDataModal');
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 将 fromPannel 的值设置到隐藏的表单中
    $('#suCaiKu input[name="upload_sucai_fromPannel"]').val(fromPannel);
    
    // 将 fromINput 的值设置到隐藏的表单中
    $('#suCaiKu input[name="upload_sucai_fromINput"]').val(fromINput);
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'createDataModal'){
        
        // 上一个面板是 createDataModal 
        // 渲染出来的关闭按钮是需要返回 createDataModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'createDataModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'editDataModal'){
        
        // 上一个面板是 editDataModal
        // 渲染出来的关闭按钮是需要返回 editDataModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'editDataModal\')">&times;</button>'
        );
    }
    
    // 开始获取素材列表
    $.ajax({
        type: "POST",
        url: "../../../public/getSuCaiList.php?p="+pageNum,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.suCaiList.length; i++) {
                    
                    // 素材ID
                    var sucai_id = res.suCaiList[i].sucai_id;
                    
                    // 素材文件名
                    var sucai_filename = res.suCaiList[i].sucai_filename;
                    
                    // 素材备注
                    var sucai_beizhu = res.suCaiList[i].sucai_beizhu;
                    
                    // 选择当前点击的素材的函数
                    var clickFunction = "selectSucaiToForm(" + sucai_id + ", '" + fromPannel.trim() + "', '"+fromINput+"')";
                    
                    var $sucaiList_HTML = $(
                    '<div class="sucai_msg" title="'+sucai_beizhu+'" onclick="'+clickFunction+'">' +
                    '   <div class="sucai_cover">' +
                    '       <img src="../../../upload/'+sucai_filename+'" />' +
                    '   </div>' +
                    '   <div class="sucai_name">'+sucai_filename+'</div>' +
                    '</div>'
                    );
                    
                    // 渲染HTML
                    $('#suCaiKu .modal-body .sucai-view').append($sucaiList_HTML);
                }
            }else{
                
                // 获取失败
                getSuCaiFail(res.msg);
            }
            
            // 分页控件
            if(res.totalNum > 12){
                
                // 渲染分页控件
                suCaifenyeControl(pageNum,fromPannel,fromINput,res.nextpage,res.prepage,res.allpage);
                
            }else{
                
                // 隐藏分页控件
                $('#suCaiKu .fenye').css('display','none');
            }
        },
        error: function() {
            
            // 服务器发生错误
            getSuCaiFail('服务器发生错误，请检查getSuCaiList.php服务是否正常！');
        }
    });
}

// 获取素材失败
function getSuCaiFail(text){
    
    $('#suCaiKu .modal-body .sucai-view').html(
        '<div class="loading">'+
        '   <img src="../../../../static/img/noRes.png" class="noRes"/>' +
        '   <br/><p>'+text+'</p>'+
        '</div>'
    );
}

// 选择当前点击的素材
function selectSucaiToForm(sucai_id,fromPannel,fromINput){
    
    $.ajax({
        type: "POST",
        url: "server/selectSucaiToForm.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 获取从哪个表单点击的
                if(fromINput == 'pic') {
                    
                    // 将选择的素材设置到 banner 这个表单
                    $('#'+fromPannel+' input[name="data_pic"]').val(res.suCaiUrl);
                }

                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",500);
                
                // 打开fromPannel的Modal
                setTimeout("showModal('"+fromPannel+"')",800);
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiForCreate.php');
        }
    });
    
    // 解决一个bug
    setTimeout("$('body').attr('class', 'modal-open')",1600);
}

// 素材库分页组件
function suCaifenyeControl(thisPage,fromPannel,fromINput,nextPage,prePage,allPage){

    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1且总页码=1
        // 无需显示分页组件
        $('#suCaiKu .fenye').css('display','none');
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1且总页码>1
        // 代表还有下一页
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button title="当前是第一页">' +
        '           <img src="../../../../static/img/firstPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button title="暂无上一页">' +
        '           <img src="../../../../static/img/prevPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示组件
        $('#suCaiKu .fenye').css('display','block');
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button title="暂无下一页">' +
        '           <img src="../../../../static/img/nextPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button title="当前是最后一页">' +
        '           <img src="../../../../static/img/lastPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示组件
        $('#suCaiKu .fenye').css('display','block');
        
    }else{
        
        // 其他情况
        // 需要显示所有组件
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示组件
        $('#suCaiKu .fenye').css('display','block');
    }
    
    // 渲染分页组件
    $('#suCaiKu .fenye').html($suCaiFenye);
}

// 获取素材库分页数据
function getSuCaiFenyeData(e){
    
    var FenyeData = e.id;
    var FenyeData_parts = FenyeData.split("_");
    var pageNum = FenyeData_parts[0]; // 页码
    var fromPannel = FenyeData_parts[1]; // 来源Modal
    var fromINput = FenyeData_parts[2]; // 来源表单
    
    // 获取该页列表
    getSuCai(pageNum,fromPannel,fromINput);
}

// 素材库的界面关闭后
// 点击右上角X会返回上一步
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏suCaiKu面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个Modal
    if(fromPannel == 'createDataModal'){
        
        showModal('createDataModal')
    }else if(fromPannel == 'editDataModal'){

        showModal('editDataModal')
    }
    
    // 解决一个bug
    setTimeout("$('body').attr('class', 'modal-open')",1600);
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