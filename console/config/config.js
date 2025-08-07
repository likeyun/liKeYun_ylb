// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的域名列表
        getDomainNameList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getDomainNameList();
    }
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

// 登录后的一些初始化
function initialize_Login(loginStatus,user_admin){
    
    if(loginStatus == 'login'){
        
        // 管理权限
        if(user_admin == 2){
            
            // 不渲染按钮
            $('#button-view').html('');
        }else{
            
            // 显示
            $('#button-view').css('display','block');
        }
    }else{
        
        // 不渲染按钮
        $('#button-view').html('');
    }
}

// 检查当前版本的代码与数据库是否搭配
// 如果不搭配，需要通过初始化操作数据库
function init1() {
    $.ajax({type: "POST",url: "init1.php",});
}

// 获取默认域名
function getDefaultDomainName(){
    
    $.ajax({
        type: "POST",
        url: "./getDefaultDomainName.php",
        success: function(res){
            
            // 将默认域名添加至选项中
            $("#default_rkym").append('<option value="'+res.default_rkym+'">'+res.default_rkym+'</option>');
            $("#default_ldym").append('<option value="'+res.default_ldym+'">'+res.default_ldym+'</option>');
            $("#default_dlym").append('<option value="'+res.default_dlym+'">'+res.default_dlym+'</option>');
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getDefaultDomainName.php');
        }
    });
}

// 加载域名列表
function getDomainNameList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getDomainNameList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getDomainNameList.php?p="+pageNum
    }
    
    // 获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getDomainNameList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>ID</th>' +
                '   <th>类型</th>' +
                '   <th>备注</th>' +
                '   <th>域名/落地页</th>' +
                '   <th>授权用户组</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                let domain_beizhu;
                for (var i=0; i<res.domainList.length; i++) {
                    
                    var xuhao = i+1;
                    var domain_id = res.domainList[i].domain_id;
                    
                    // 类型
                    if(res.domainList[i].domain_type == 1){
                        
                        // 入口域名
                        var domain_type = '<span class = "light-tag">入口域名</span>';
                    }else if(res.domainList[i].domain_type == 2){
                        
                        // 落地域名
                        var domain_type = '<span class = "light-tag">落地域名</span>';
                    }else if(res.domainList[i].domain_type == 3){
                        
                        // 短链域名
                        var domain_type = '<span class = "light-tag">短链域名</span>';
                    }else if(res.domainList[i].domain_type == 4){
                        
                        // 备用域名
                        var domain_type = '<span class = "light-tag">备用域名</span>';
                    }else if(res.domainList[i].domain_type == 5){
                        
                        // 对象存储域名
                        var domain_type = '<span class = "light-tag">对象存储域名</span>';
                    }else if(res.domainList[i].domain_type == 6){
                        
                        // 轮询域名
                        var domain_type = '<span class = "light-tag">轮询域名</span>';
                    }
                    
                    // 域名
                    var domain = res.domainList[i].domain;
                    
                    // 备注
                    if(res.domainList[i].domain_beizhu || res.domainList[i].domain_beizhu !== null) {
                        
                        // 有备注信息
                        domain_beizhu = res.domainList[i].domain_beizhu + ' 🖌';
                    }else {
                        
                        // 没有
                        domain_beizhu = '🖌';
                    }
                    
                    // 授权用户组
                    var domain_usergroup = res.domainList[i].domain_usergroup;
                    if(domain_usergroup) {
                        
                        // 取出JSON数组
                        var domain_usergroup_Array = JSON.parse(domain_usergroup.replace(/'/g, "\""));
                        var result_domain_usergroup = "";
                        domain_usergroup_Array.forEach(function(domain_usergroup_, index) {
                            result_domain_usergroup += domain_usergroup_;
                            if (index < domain_usergroup_Array.length - 1) {
                                result_domain_usergroup += "、";
                            }
                        });
                        
                        // 拼接渲染数据
                        var domain_usergroup_data = '<span style="max-width:300px;display:block;">' + result_domain_usergroup + '，<span onclick="getSelectedUsergroup('+domain_id+')" class="add_usergroup" data-toggle="modal" data-target="#addUsergroupModal">添加</span></span>';
                    }else {
                        
                        var domain_usergroup_data = 
                        '<span style="max-width:300px;display:block;">' +
                            '<span>未添加，</span>' +
                            '<span onclick="getSelectedUsergroup('+domain_id+')" class="add_usergroup" data-toggle="modal" data-target="#addUsergroupModal">添加</span>' +
                        '</span>';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+domain_id+'</td>' +
                        '   <td>'+domain_type+'</td>' +
                        '   <td onclick="update_beizhu('+domain_id+')" style="cursor:pointer;" title="点击修改备注">'+domain_beizhu+'</td>' +
                        '   <td style="max-width:400px;word-break: break-word;">'+domain+'</td>' +
                        '   <td>'+domain_usergroup_data+'</td>' +
                        '   <td data-toggle="modal" id="'+domain_id+'" data-target="#DelDomainModal" onclick="askDelDomainName(this);"><span class="light-tag" style="cursor:pointer;">删除</span></td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页
                if(res.page == 1 && res.allpage == 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 无需显示分页控件
                    $("#right .data-card .fenye").css("display","none");
                }else if(res.page == 1 && res.allpage > 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 代表还有下一页
                    var $domainNameFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                    $('.fenye').css('width','80px');
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $domainNameFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                    $('.fenye').css('width','80px');
                }else{
                    
                    var $domainNameFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                    $('.fenye').css('width','150px');
                }
                
                // 渲染分页控件
                $("#right .data-card .fenye").html($domainNameFenye_HTML);
                
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 如果是未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 205状态码：无管理权限
                if(res.code == 205){
                    
                    // 无管理权限
                    noLimit(res.msg);
                    $('.data-list').remove();
                    $('.button-view').remove();
                    $('.fenye').remove();
                }else{
                    
                    // 无数据
                    noData(res.msg);
                }
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getDomainNameList.php');
        
        // 隐藏顶部按钮
        $('#right .button-view').html('');
      },
    });
}

// 首次使用初始化
function init2() {
    
    $.ajax({
        type: "POST",
        url: "init2.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 初始化完成
                $("#domainCheckTasksModal .modal-body").html(
                    '<p class="init-text">'+res.msg+'</p>' +
                    '<button class="tint-btn center-btn" onclick="location.reload();">点击这里刷新后使用</button>'
                );
                return;
            }else{
                
                // 失败
                $("#domainCheckTasksModal .modal-body").html(
                    '<p class="init-text">'+res.msg+'</p>' +
                    '<button class="tint-btn center-btn" onclick="init2()">点击这里初始化</button>'
                );
                return;
            }
        },
        error: function() {
            
            // 服务器发生错误
            $("#domainCheckTasksModal .modal-body").html(
                '<p class="init-text">服务器发生错误</p>' +
                '<button class="tint-btn center-btn" onclick="init2()">点击这里初始化</button>'
            );
            return;
        },
        beforeSend: function() {
            
            $("#domainCheckTasksModal .modal-body").html(
                '<p class="init-text">请稍等...</p>' +
                '<button class="tint-btn center-btn" onclick="location.reload();">正在初始化...</button>'
            );
            return;
        },
    });
}

// 获取当前域名已选择的用户组
// 并且进行设置用户组
function getSelectedUsergroup(domain_id) {
    
    $("#selectedTags").html('');
    $("#availableTags").html('');
    $('#addUsergroupModal .domain_id').val(domain_id);

    $.ajax({
        type: "POST",
        url: "./getSelectedUsergroup.php?domain_id=" + domain_id,
        success: function(res){
            
            // 获取成功
            // 已选的用户组
            var selectedUsergroupArray = res.domain_usergroup;
            
            // 可选的用户组
            var availableUsergroupArray = res.usergroupList;
            
            // 初始化已选中的用户组
            function initializeSelectedTags() {
                
                var selectedTags = $("#selectedTags");
                $.each(selectedUsergroupArray, function(index, value) {
                    var tag = $("<span>").text(value).addClass("usergroup_selected");
                    tag.click(toggleTag);
                    selectedTags.append(tag);
                });
            }
            
            // 初始化可选标签
            function initializeAvailableTags() {
                
                var availableTags = $("#availableTags");
                $.each(availableUsergroupArray, function(index, value) {
                    var tag = $("<span>").text(value).addClass("unselected");
                    tag.click(toggleTag);
                    availableTags.append(tag);
                });
            }
            
            // 新的选中项（在已选的基础上添加新的项）
            newUsergroupArray = selectedUsergroupArray;
            
            // 切换标签的选中状态
            function toggleTag() {
                
                // 获取当前点击的标签
                var tag = $(this);
                var text = tag.text();
            
                if (tag.hasClass("usergroup_selected")) {
                    
                    // 移除选中样式
                    tag.removeClass("usergroup_selected").addClass("unselected");
                    
                    // 移除选中项
                    newUsergroupArray = newUsergroupArray.filter(item => item != text)
                } else {
                    
                    // 添加选中样式
                    tag.removeClass("unselected").addClass("usergroup_selected");
                
                    // 添加选中项
                    newUsergroupArray.push(text);
                }
                
                // 将新的选中项设置到表单中
                $('#addUsergroupModal .newUsergroupArray').val(newUsergroupArray);
                
                // 打印新的选中项
                console.log(newUsergroupArray)
            }
            
            // 初始化
            initializeSelectedTags();
            initializeAvailableTags();
        },
        error: function() {
            
            // 获取失败
            showErrorResultForphpfileName('getSelectedUsergroup.php');
        }
    });
}

// 设置当前域名的用户组
function setUsergroup() {
    
    // 获取已选的项以及ID
    const newUsergroupArray = $('#addUsergroupModal .newUsergroupArray').val();
    const domain_id = $('#addUsergroupModal .domain_id').val();
    
    // 提交
    $.ajax({
        type: "GET",
        url: "./setUsergroup.php?newUsergroupArray=" + newUsergroupArray + "&domain_id=" + domain_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
               
                showSuccessResult(res.msg);
                
                setTimeout('hideModal("addUsergroupModal")', 500);
                
                // 获取新的列表
                setTimeout('getDomainNameList()', 800);
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 获取失败
            showErrorResultForphpfileName('setUsergroup.php');
        }
    });
}

// 修改备注
function update_beizhu(domain_id) {

    if(domain_id) {
        var beizhu = prompt("输入备注信息", "");
        if (beizhu !== null) {
            
            $.ajax({
                type: "GET",
                url: "./update_beizhu.php?beizhu=" + beizhu + "&domain_id=" + domain_id,
                success: function(res){
                    
                    // 成功
                    if(res.code == 200){
                        
                        getDomainNameList();
                    }else{
                        
                        alert(res.msg);
                    }
                },
                error: function() {
                    alert('update_beizhu.php服务器发生错误')
                }
            });
        }
    }
}

// 获取通知渠道配置
function getNotificationConfig(){
    
    $.ajax({
        type: "POST",
        url: "./getNotificationConfig.php",
        success: function(res){
            
            if(res.code == 200){
                
                // 将配置信息填写至表单
                $('#notiConfigModal input[name="corpid"]').val(res.notificationConfig.corpid);
                $('#notiConfigModal input[name="corpsecret"]').val(res.notificationConfig.corpsecret);
                $('#notiConfigModal input[name="touser"]').val(res.notificationConfig.touser);
                $('#notiConfigModal input[name="agentid"]').val(res.notificationConfig.agentid);
                $('#notiConfigModal input[name="bark_url').val(res.notificationConfig.bark_url);
                $('#notiConfigModal input[name="email_acount"]').val(res.notificationConfig.email_acount);
                $('#notiConfigModal input[name="email_pwd"]').val(res.notificationConfig.email_pwd);
                $('#notiConfigModal input[name="email_receive').val(res.notificationConfig.email_receive);
                $('#notiConfigModal input[name="email_smtp"]').val(res.notificationConfig.email_smtp);
                $('#notiConfigModal input[name="email_port"]').val(res.notificationConfig.email_port);
                $('#notiConfigModal input[name="SendKey"]').val(res.notificationConfig.SendKey);
                $('#notiConfigModal input[name="http_url"]').val(res.notificationConfig.http_url);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看getNotificationConfig.php的返回信息进行排查！')
        }
    });
}

// 保存通知渠道配置
function notiConfig() {
    
    $.ajax({
        type: "POST",
        url: "./notiConfig.php",
        data: $('#notiConfig').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                showSuccessResult(res.msg);
                setTimeout("hideModal('notiConfigModal')",600);
                setTimeout("showNotification('"+res.msg+"')",800);
            }else{
                
                // 保存失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误！可按F12打开开发者工具点击Network或网络查看editNotificationConfig.php的返回信息进行排查！');
        }
    });
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getDomainNameList(pageNum);
}

// 测试一下（企业微信）
function testQywx(){
    
    // 获取表单参数
    const corpid = $('#notiConfigModal input[name="corpid"]').val();
    const corpsecret = $('#notiConfigModal input[name="corpsecret"]').val();
    const touser = $('#notiConfigModal input[name="touser"]').val();
    const agentid = $('#notiConfigModal input[name="agentid"]').val();
    
    if(corpid && corpsecret && touser && agentid) {
        
        // 发送测试
        $.ajax({
            type: "GET",
            url: "../public/qywx.php?noti_text=企业微信通知测试",
            success: function(res){
                
                // 成功
                if(res.errcode == 0 && res.errmsg == "ok"){
                    
                    alert('已发送测试消息，请自行前往手机查看企业微信通知。')
                }else{
                    
                    // 失败
                    alert(res.errcode)
                }
            },
            error: function() {
                
                // 服务器发生错误
                alert('服务器发生错误')
            }
        });
    }
}

// 测试一下（电子邮件）
function testEmail(){
    
    // 获取表单参数
    const email_acount = $('#notiConfigModal input[name="email_acount"]').val();
    const email_pwd = $('#notiConfigModal input[name="email_pwd"]').val();
    const email_smtp = $('#notiConfigModal input[name="email_smtp"]').val();
    const email_port = $('#notiConfigModal input[name="email_port"]').val();
    const email_receive = $('#notiConfigModal input[name="email_receive"]').val();
    
    if(email_acount && email_pwd && email_smtp && email_port && email_receive) {
        
        // 发送测试
        $.ajax({
            type: "GET",
            url: "../public/emailSend/?noti_text=电子邮件通知测试&aqm=123456",
            success: function(res){
                
                // 成功
                alert('已发送测试消息到你的电子邮箱，请注意查收。');
            },
            error: function() {
                
                // 服务器发生错误
                alert('服务器发生错误')
            }
        });
    }
}

// 复制定时任务URL
function copyURL(element) {

    var url = element.getAttribute('data-url');
    var tempInput = document.createElement('input');
    tempInput.value = url;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    element.textContent = "已复制";
    setTimeout(function() {
        element.textContent = "复制URL";
    }, 2000);
}

// 获取用户组的页面权限列表
function getUsergroupsPermissions() {
    $(".usergroup-list .usergroup-table tbody").empty('');
    $.getJSON('getUsergroupsPermissions.php', function (res) {
        if (res.code == 200) {
            if (res.getNavList.length > 0) {
                for (var i = 0; i < res.getNavList.length; i++) {
                    const navList = JSON.parse(res.getNavList[i].navList);

                    // 提取每项的 text 字段，并用顿号拼接
                    const permission_nav = navList.map(item => item.text).join('、');

                    // 构建表格 HTML
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td><span class="light-tag">' + res.getNavList[i].usergroup_name + '</span></td>' +
                        '   <td>' + permission_nav + '</td>' +
                        '   <td>' +
                        '       <span class="click-tag" data-usergroup="' + res.getNavList[i].usergroup_name + '" onclick="ChangePagePermission(this)">编辑</span>' +
                        '   </td>' +
                        '</tr>'
                    );

                    $(".usergroup-list .usergroup-table tbody").append($tbody_HTML);
                }
            } else {
                
                // 无数据
                $(".usergroup-list .usergroup-table tbody").html('<tr><td colspan="3" style="text-align:center;color:#999;">暂无用户组权限数据</td></tr>');
            }
        } else {
            
            // 获取失败
            console.error('获取失败：', res.message || '未知错误');
        }
    });
}

// 修改页面权限
let allNavList = [];
function ChangePagePermission(e) {
    const usergroup = e.dataset.usergroup;
    if (usergroup) {
        showModal('ChangePagePermissionModal');
        hideModal('pagePermissionModal');

        $.getJSON('getNavList.php?usergroup=' + usergroup, function (res) {
            $('#currentUser_usergroup').text(res.currentUser_usergroup);
            const $navList = $('.navList');
            $navList.empty();

            allNavList = res.allNavList; // 存为全局变量
            const currentNavList = res.navList.map(item => item.href);

            allNavList.forEach(item => {
                const isSelected = currentNavList.includes(item.href);

                const $tag = $('<span></span>')
                    .addClass('nav-tag')
                    .toggleClass('selected', isSelected)
                    .attr('data-href', item.href)
                    .text(item.text);

                $tag.on('click', function () {
                    $(this).toggleClass('selected');
                    // 可用于实时调试
                });

                $navList.append($tag);
            });
        });
    }
}

// 获取已选的页面
function getSelectedNavList() {
    const selectedHrefs = $('.nav-tag.selected').map(function () {
        return $(this).data('href');
    }).get();
    return allNavList.filter(item => selectedHrefs.includes(item.href));
}

// 保存授权
function savePermission() {
    
    // 获取当前用户组
    const currentUsergroup = $('#currentUser_usergroup').text();
    const SelectedNavList = getSelectedNavList();
    
    if(currentUsergroup && SelectedNavList) {
        
        // 提交
        $.ajax({
            type: "GET",
            url: "./savePermission.php?usergroup="+currentUsergroup+"&SelectedNavList=" + JSON.stringify(SelectedNavList),
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // 保存成功
                    showSuccessResult(res.msg);
                    setTimeout(function() {
                        hideAndShowModal('ChangePagePermissionModal','pagePermissionModal');
                        hideResult();
                        getUsergroupsPermissions();
                    }, 500);
                }else{
                    
                    // 失败
                    showErrorResult(res.msg);
                }
            },
            error: function() {
                
                // 服务器发生错误
                alert('savePermission.php服务器发生错误')
            }
        });
    }
}

// 隐藏一个Modal
// 再打开另一个Modal
function hideAndShowModal(currentModalId, backModalId) {
    hideModal(currentModalId);
    showModal(backModalId);
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1.5秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 添加域名
function addDomainName(){
    
    $.ajax({
        type: "POST",
        url: "./addDomainName.php",
        data: $('#addDomainName').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                setTimeout('hideModal("addDomainNameModal")', 500);
                
                // 重新加载域名列表
                setTimeout('getDomainNameList()', 500);
                
                // 成功
                setTimeout('showNotification("'+res.msg+'")',800);
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('addDomainName.php');
        }
    });
}

// 询问是否要删除
function askDelDomainName(e){
    
    // 获取domain_id
    var domain_id = e.id;
    
    // 将群id添加到button的
    // delDomainName函数用于传参执行删除
    $('#DelDomainModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delDomainName('+domain_id+');">确定删除</button>'
    )
}

// 删除域名
function delDomainName(domain_id){
    
    $.ajax({
        type: "GET",
        url: "./delDomainName.php?domain_id="+domain_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("DelDomainModal");
                
                // 重新加载域名列表
                setTimeout('getDomainNameList()', 500);
                
                // 显示全局信息提示弹出提示
                showNotification(res.msg);
            }else{
                
                // 失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delDomainName.php');
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
    
    $("#domainCheckTasksModal .tasks-list").css('display','none');
    $("#domainCheckTasksModal .noData").html(
        '<img src="../../static/img/noData.png" class="noDataIMG" /><br/>' +
        '<p class="noDataText">'+text+'</p>'
    );
    $("#domainCheckTasksModal .noData").css('display','block');
}

// 无管理权限
function noLimit(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noLimit.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化（加载域名列表）
function initialize_getDomainNameList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（添加域名）
function initialize_addDomainName(){
    $("#domain").val('');
    $("#domain_beizhu").val('');
    $("#domain_type").val('');
    hideResult();
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

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
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

console.log('%c 欢迎使用引流宝','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');