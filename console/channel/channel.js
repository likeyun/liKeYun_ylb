window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的渠道码数据列表
        getChannelList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getChannelList();
    }
    
    // clipboard插件
    var clipboard = new ClipboardJS('#shareChannelHm .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#shareChannelHm .modal-footer button').text('已复制');
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
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">'+res.user_name+' <span onclick="exitLogin();" class="exitLogin">退出</span></div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('login')
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
            errorPage();
        }
    });
}

// 登录后的一些初始化
function initialize_Login(loginStatus){
    
    if(loginStatus == 'login'){
        
        // 显示创建按钮
        $('#button-view').css('display','block');
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取渠道码列表
function getChannelList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getChannelList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getChannelList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getchannelList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>ID</th>' +
                '   <th>标题</th>' +
                '   <th>备注</th>' +
                '   <th>访问限制</th>' +
                '   <th>设备访问量</th>' +
                '   <th>访问数据</th>' +
                '   <th>创建时间</th>' +
                '   <th>状态</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.channelList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    // var xuhao = i+1;
                    
                    // （2）标题
                    var channel_id = res.channelList[i].channel_id;
                    var channel_title = res.channelList[i].channel_title;
                    
                    // 状态
                    if(res.channelList[i].channel_status == '1'){
                        
                        // 正常
                        var channel_status = 
                        '<span class="switch-on" id="'+res.channelList[i].channel_id+'" onclick="changeChannelStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }else{
                        
                        // 关闭
                        var channel_status = 
                        '<span class="switch-off" id="'+res.channelList[i].channel_id+'" onclick="changeChannelStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }
                    
                    // （4）创建时间
                    var channel_creat_time = res.channelList[i].channel_creat_time;
                    
                    // （5）访问量
                    var channel_pv = res.channelList[i].channel_pv;
                    
                    // （6）渠道码ID
                    var channel_id = res.channelList[i].channel_id;
                    
                    // 设备数据量
                    var Android_Total = res.channelList[i].Android_Total;
                    var iOS_Total = res.channelList[i].iOS_Total;
                    var Windows_Total = res.channelList[i].Windows_Total;
                    var Linux_Total = res.channelList[i].Linux_Total;
                    var MacOS_Total = res.channelList[i].MacOS_Total;
                    
                    // 数据量
                    var channel_DataTotal = res.channelList[i].channel_DataTotal;
                    
                    // 今天访问量
                    var channel_today_pv = JSON.parse(res.channelList[i].channel_today_pv.toString()).pv;
                    var channel_today_date = JSON.parse(res.channelList[i].channel_today_pv.toString()).date;
                    
                    // 获取日期
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const todayDate = `${year}-${month}-${day}`;
                    
                    if(channel_today_date == todayDate){
                        
                        // 日期一致
                        // 显示今天的访问量
                        var channel_pv_today = channel_today_pv;
                    }else{
                        
                        // 日期不一致
                        // 显示0
                        var channel_pv_today = 0;
                    }
                    
                    // 各渠道设备访问量tag
                    var channel_device_pv_tags = `
                        <div>
                            <span class="light-tag" title="Android设备访问量">
                                <span class="icon-view android-icon"></span>
                                <span class="tag-text">${Android_Total}</span>
                            </span>
                            <span class="light-tag" title="iOS设备访问量">
                                <span class="icon-view ios-icon"></span>
                                <span class="tag-text">${iOS_Total}</span>
                            </span>
                            <span class="light-tag" title="Windows设备访问量">
                                <span class="icon-view windows-icon"></span>
                                <span class="tag-text">${Windows_Total}</span>
                            </span>
                            <span class="light-tag" title="Linux设备访问量">
                                <span class="icon-view linux-icon"></span>
                                <span class="tag-text">${Linux_Total}</span>
                            </span>
                            <span class="light-tag" title="MacOS设备访问量">
                                <span class="icon-view macos-icon"></span>
                                <span class="tag-text">${MacOS_Total}</span>
                            </span>
                        </div>`;
                    
                    const limitMap = {
                        1: '不限制',
                        2: '仅限微信内打开',
                        3: '仅限QQ内打开',
                        4: '仅限手机打开',
                        5: '仅限微信外的手机浏览器打开',
                        6: '仅限QQ外的手机浏览器打开',
                        7: '仅限电脑打开',
                        8: '仅限Android设备打开',
                        9: '仅限iOS设备打开'
                    };
                    let channel_limit = limitMap[res.channelList[i].channel_limit] || '未知';
                    
                    // 仅限后台可见的备注信息
                    let channel_beizhu_ht;
                    if(res.channelList[i].channel_beizhu_ht){
                        
                        // 有数据
                        channel_beizhu_ht = res.channelList[i].channel_beizhu_ht;
                    }else{
                        
                        // 无数据
                        channel_beizhu_ht = '-';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr style="white-space: nowrap;">' +
                        '   <td>'+channel_id+'</td>' +
                        '   <td>'+channel_title+'</td>' +
                        '   <td>'+channel_beizhu_ht+'</td>' +
                        '   <td>'+channel_limit+'</td>' +
                        '   <td>'+channel_device_pv_tags+'</td>' +
                        '   <td>' +
                        '       <div>总访问量：'+channel_pv+'</div>' + 
                        '       <div>今天访问：'+channel_pv_today+'</div>' + 
                        '       <div>访问记录：'+channel_DataTotal+'</div>' + 
                        '   </td>' +
                        '   <td>'+channel_creat_time+'</td>' +
                        '   <td>'+channel_status+'</td>' +
                        '   <td class="cz-tags">' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#shareChannelHm" onclick="shareChannel('+channel_id+')">分享</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#editChannelModal" onclick="getChannelInfo(this)" id="'+channel_id+'">编辑</span>' +
                        '       <a class="light-tag" href="./channelData.html?channelid='+channel_id+'" title="查看当前渠道的数据">数据</a>' +
                        '       <span class="light-tag" onclick="resetChannelPv('+channel_id+')" title="重置总访问量和今日访问量">重置</span>' +
                        '       <span class="light-tag" id="'+channel_id+'" data-toggle="modal" data-target="#DelChannelHm" onclick="askDelChannel(this)">删除</span>' +
                        '   </td>' +
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
                    var $ChannelFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $ChannelFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $ChannelFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }
                // 渲染分页控件
                $("#right .data-card .fenye").html($ChannelFenye_HTML);
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
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
        errorPage('data-list','getChannelList.php');
      },
    });
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getChannelList(pageNum);
}

// 创建渠道码
function creatChannel(){
    $.ajax({
        type: "POST",
        url: "./createChannel.php",
        data: $('#creatChannel').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("CreateChannelModal")', 500);
                
                // 重新加载客服码列表
                setTimeout('getChannelList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createChannel.php');
        }
    });
}

// 编辑渠道码
function editChannel(){
    $.ajax({
        type: "POST",
        url: "./editChannel.php",
        data: $('#editChannel').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editChannelModal")', 500);
                
                // 重新加载渠道码列表
                setTimeout('getChannelList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editChannel.php');
        }
    });
}

// 询问是否要删除渠道活码
function askDelChannel(e){
    
    // 获取channel_id
    var channel_id = e.id;
    
    // 将群id添加到button的
    // delChannel函数用于传参执行删除
    $('#DelChannelHm .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delChannel('+channel_id+');">确定删除</button>'
    )
}

// 删除渠道码
function delChannel(channel_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delChannel.php?channel_id="+channel_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("DelChannelHm");
                
                // 重新加载群列表
                setTimeout('getChannelList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delChannel.php');
        }
    });
}

// 获取渠道码详情
function getChannelInfo(e){

    // 获取channel_id
    var channel_id = e.id;
    
    $.ajax({
        type: "GET",
        url: "./getChannelInfo.php?channel_id="+channel_id,
        success: function(res){

            if(res.code == 200){
                
                // 操作成功
                showSuccessResult(res.msg)
                
                // 标题
                $('input[name="channel_title"]').val(res.channelInfo.channel_title);
                
                // 后台备注
                $('input[name="channel_beizhu_ht"]').val(res.channelInfo.channel_beizhu_ht);
                
                // 获取域名列表
                getDomainNameList('edit')
                
                // 推广链接
                $('textarea[name="channel_url"]').val(res.channelInfo.channel_url);
                
                // channel_id
                $('input[name="channel_id"]').val(channel_id);

                // 定制的
                $('select[name="channel_limit"]').val(res.channelInfo.channel_limit);
                $('select[name="is_mzfwxz"]').val(res.channelInfo.is_mzfwxz);
                $('input[name="mzfwxz_url"]').val(res.channelInfo.mzfwxz_url);
                
                if(res.channelInfo.channel_limit > 1) {
                    
                    // 显示
                    $('.is_mzfwxz').css('display','block');
                }else {
                    
                    // 隐藏
                    $('.is_mzfwxz').css('display','none');
                }
                
                if(res.channelInfo.is_mzfwxz == '2' && res.channelInfo.channel_limit > 1) {
                    
                    // 显示
                    $('.mzfwxz_url').css('display','block');
                }else {
                    
                    // 隐藏
                    $('.mzfwxz_url').css('display','none');
                }
                
                // 获取当前设置的域名
                $('select[name="channel_rkym"]').val(res.channelInfo.channel_rkym);
                $('select[name="channel_ldym"]').val(res.channelInfo.channel_ldym);
                $('select[name="channel_dlym"]').val(res.channelInfo.channel_dlym);
            }else{
                
                // 操作失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getChannelInfo.php');
        }
    });
}

// 监听访问限制的切换
$(document).on('change', '#editChannelModal select[name="channel_limit"]', function () {
    const selectedValue = $(this).val();

    // 这里写你的业务逻辑
    if(selectedValue == '1') {
        
        // 全部隐藏
        $('.is_mzfwxz').css('display','none');
        $('.mzfwxz_url').css('display','none');
    }else {
        
        // 全部显示
        $('.is_mzfwxz').css('display','block');
        $('.mzfwxz_url').css('display','block');
    }
});

// 监听命中跳转URL的切换
$(document).on('change', '#editChannelModal select[name="is_mzfwxz"]', function () {
    const selectedValue = $(this).val();

    // 这里写你的业务逻辑
    if(selectedValue == '1') {
        
        // 隐藏mzfwxz_url
        $('.mzfwxz_url').css('display','none');
    }else {
        
        // 显示mzfwxz_url
        $('.mzfwxz_url').css('display','block');
    }
});

// 使用appendOptionsToSelect函数来为每个select元素处理选项的添加
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
                
                // 将入口、落地、短链域名添加至选项中
                appendOptionsToSelect($("select[name='channel_rkym']"), res.rkymList);
                appendOptionsToSelect($("select[name='channel_ldym']"), res.ldymList);
                appendOptionsToSelect($("select[name='channel_dlym']"), res.dlymList);
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

// 分享渠道码
function shareChannel(channel_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "./shareChannel.php?channel_id="+channel_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").text(res.longUrl);
                
                // 短链接
                $("#shortUrl").html('<span id="channel_'+channel_id+'">'+res.shortUrl+'</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#shareChannelHm .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#channel_'+channel_id+'">复制链接</button>'
                );
                
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareChannel.php');
        }
    });
}

// 重置访问量
function resetChannelPv(channel_id){
    
    if(confirm("确定要重置？")) {
        
        $.ajax({
            type: "POST",
            url: "resetChannelPv.php?channel_id=" + channel_id,
            success: function(res){
                
                // 成功
                showNotification(res.msg);
                setTimeout('getChannelList()',500);
            },
            error: function() {
                
                // 服务器发生错误
                showNotification('服务器发生错误')
            }
        });
    }
}

// 切换switch
// changeChannelStatus
function changeChannelStatus(e){
    
    $.ajax({
        type: "POST",
        url: "./changeChannelStatus.php?channel_id=" + e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 获取列表
                getChannelList();
            }else{
                
                // 非200状态码
                showErrorResult(res.msg)
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

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
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

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
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

// 初始化（getchannelList获取渠道码列表）
function initialize_getchannelList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化
// 获取域名列表
function initialize_getDomainNameList(module){
    
    // 默认值
    $('#CreateChannelModal input[name="channel_title"]').val('');
    $('#CreateChannelModal textarea[name="channel_url"]').val(''); // 定制优化
    $('select[name="channel_rkym"]').empty();
    $('select[name="channel_ldym"]').empty();
    $('select[name="channel_dlym"]').empty();
    
    // 定制新增
    $('select[name="channel_limit"]').val('1');
    $('.is_mzfwxz').css('display','none');
    $('select[name="is_mzfwxz"]').val('1');
    $('.mzfwxz_url').css('display','none');
    $('input[name="mzfwxz_url"]').val('');
    
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

// 获取URL参数
function queryURLParams(url) {
    var pattern = /(\w+)=(\w+)/ig;
    var parames = {};
    url.replace(pattern, ($, $1, $2) => {
        parames[$1] = $2;
    });
    return parames;
}