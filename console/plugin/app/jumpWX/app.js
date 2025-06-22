
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

// 获取列表
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
                '   <th>jwid</th>' +
                '   <th>抖音卡片样式</th>' +
                '   <th>备注</th>' +
                '   <th>投放平台</th>' +
                '   <th>目标地址</th>' +
                '   <th>访问次数</th>' +
                '   <th>按钮点击</th>' +
                '   <th title="达到这个访问量页面就无法使用">访问量限制</th>' +
                '   <th>创建/到期</th>' +
                '   <th class="createUser-node">用户</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.jwList.length; i++) {
                    
                    // 主标题与副标题
                    var douyin_card_html = `
                        <span class="card-view">
                            <span class="card-cover">
                                <img src="${res.jwList[i].jw_icon}" />
                            </span>
                            <span class="card-info">
                                <span class="card-title">${res.jwList[i].jw_title}</span>
                                <span class="card-desc">${res.jwList[i].jw_beizhu}</span>
                            </span>
                        </span>
                    `;
                    
                    // 创建时间和到期时间的处理
                    if(getExpirationStatus(res.jwList[i].jw_expire_time) == 2) {
                        
                        // 即将过期
                        var datetime_html = `
                            <span class="datetime-td">创建 ${res.jwList[i].jw_create_time}</span>
                            <span class="datetime-td-yello-text">即将到期 ${res.jwList[i].jw_expire_time}</span>
                        `;
                    }else if(getExpirationStatus(res.jwList[i].jw_expire_time) == 1) {
                        
                        // 正常样式
                        var datetime_html = `
                            <span class="datetime-td">创建 ${res.jwList[i].jw_create_time}</span>
                            <span class="datetime-td">到期 ${res.jwList[i].jw_expire_time}</span>
                        `;
                    }else {
                        
                        // 已到期
                        var datetime_html = `
                            <span class="datetime-td">创建 ${res.jwList[i].jw_create_time}</span>
                            <span class="datetime-td-red-text">已到期 ${res.jwList[i].jw_expire_time}</span>
                        `;
                    }
                    
                    // 到期计算
                    function getExpirationStatus(targetTime) {
                        
                        // 解析目标时间字符串
                        let targetDate = new Date(targetTime.replace(/-/g, '/')); // 兼容 iOS 解析
                        let now = new Date();
                    
                        // 计算提前 2 天的时间
                        let warningDate = new Date(targetDate);
                        warningDate.setDate(warningDate.getDate() - 2);
                    
                        if (now < warningDate) {
                            return 1;  // 当前时间 < 提前2天的时间
                        } else if (now >= warningDate && now < targetDate) {
                            return 2; // 进入提前2天的提醒期
                        } else {
                            return 3;  // 当前时间已经超过目标时间
                        }
                    }
                    
                    // 访问次数
                    var jw_pv = res.jwList[i].jw_pv;
                    
                    // 访问量限制
                    var jw_fwl_limit = res.jwList[i].jw_fwl_limit;
                    
                    // 点击次数
                    var jw_clickNum = res.jwList[i].jw_clickNum;
                    
                    // URLScheme
                    var jw_url = res.jwList[i].jw_url;
                    
                    // 后台的备注信息
                    var jw_beizhu_msg = res.jwList[i].jw_beizhu_msg ? res.jwList[i].jw_beizhu_msg : ' - ';
                    
                    // 投放平台
                    var jw_platform = res.jwList[i].jw_platform;
                    var platformMap = {
                        'douyin': '<img src="img/jw_platform_icon_douyin.png" width="20" /> 抖音',
                        'kuaishou': '<img src="img/jw_platform_icon_kuaishou.png" width="20" /> 快手',
                        'qq': '<img src="img/jw_platform_icon_qq.png" width="20" /> QQ',
                        'xhs': '<img src="img/jw_platform_icon_xhs.png" width="20" /> 小红书',
                        'sms': '<img src="img/jw_platform_icon_sms.png" width="20" /> 短信',
                        'weibo': '<img src="img/jw_platform_icon_weibo.png" width="20" /> 微博',
                        'zhihu': '<img src="img/jw_platform_icon_zhihu.png" width="20" /> 知乎',
                        'common': '<img src="img/jw_platform_icon_common.png" width="20" /> 通用'
                    };
                    var jw_platform_text = jw_platform ? (platformMap[jw_platform] || ' - ') : ' - ';
                    
                    // ID
                    var jw_id = res.jwList[i].jw_id;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.jwList[i].jw_id+'</td>' +
                        '   <td>'+douyin_card_html+'</td>' +
                        '   <td>'+jw_beizhu_msg+'</td>' +
                        '   <td>'+jw_platform_text+'</td>' +
                        '   <td>' +
                        '       <a href="javascript:;" id="'+jw_url+'" class="jwurl-td" onclick="showJwUrl(this)">查看</a> | ' +
                        '       <a href="javascript:;" id="'+jw_url+'" class="jwurl-td" onclick="copyJwUrl(this)">复制</a>' +
                        '   </td>' +
                        '   <td>'+jw_pv+'</td>' +
                        '   <td>'+jw_clickNum+'</td>' +
                        '   <td title="达到这个访问量页面就无法使用">' + (jw_fwl_limit ? jw_fwl_limit : '不限制') + '</td>' +
                        '   <td>'+datetime_html+'</td>' +
                        '   <td class="createUser-node">' + res.jwList[i].jw_create_user + '</td>' +
                        '   <td>' + 
                        '       <span class="cz-tag" data-toggle="modal" data-target="#ShareJwModal" onclick="shareJw('+jw_id+',1)">分享</span>' +
                        '       <span class="cz-tag" data-toggle="modal" data-target="#editJwModal" onclick="getJwInfo('+jw_id+')">编辑</span>' +
                        '       <span class="cz-tag" data-toggle="modal" data-target="#delJwModal" onclick="askDelJw('+jw_id+')">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 如果不是超管
                if(res.user_admin.toString() !== '1') {
                    
                    // 移除创建人这一列
                    $('.createUser-node').remove();
                    
                    // 移除搜索框
                    $('.search-view').remove();
                }else {
                    
                    // 展示搜索框
                    $('.search-view').css('display','block');
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
        errorPage('data-list','getZjyList.php');
      },
    });
}

// 查看JwUrl
function showJwUrl(e) {
    if(e.id) {
        
        showMessage("目标地址：" + e.id, 2000);
    }else{
        
        showMessage('暂无数据', 2000);
    }
}

// 复制JwUrl
function copyJwUrl(elem) {
    
    // 获取
    const text = elem.id;
    const originalText = elem.textContent;
    
    // 创建一个临时的 input 元素来复制文本
    const input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    input.setSelectionRange(0, 99999); // 移动端兼容
    
    // 执行复制
    try {
        document.execCommand('copy');
        elem.textContent = '已复制';
        
        // 2 秒后恢复原内容
        setTimeout(() => {
          elem.textContent = originalText;
        }, 2000);
    } catch (err) {
        console.error('复制失败：', err);
    }
    
    // 移除临时 input 元素
    document.body.removeChild(input);
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
    getJwList(pageNum);
}

// 创建链接
function createJw(){
    
    $.ajax({
        type: "POST",
        url: "server/createJw.php",
        data: $('#createJw').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏modal
                setTimeout('hideModal("createJwModal")', 500);
                
                // 重新加载列表
                setTimeout('getJwList();', 500);
                
                // 显示操作结果
                setTimeout(function(){
                    showMessage(res.msg, 2000);
                },500)
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
                
                // 显示操作结果
                setTimeout(function(){
                    showMessage(res.msg, 2000);
                },500)
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

// 获取外链详情
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
                $('select[name="jw_common_landpage"]').append(
                    '<option value="'+res.jwInfo.jw_common_landpage+'">'+res.jwInfo.jw_common_landpage+'</option>'
                );
                $('select[name="jw_douyin_landpage"]').append(
                    '<option value="'+res.jwInfo.jw_douyin_landpage+'">'+res.jwInfo.jw_douyin_landpage+'</option>'
                );
                
                // 分享图
                $('#editJwModal input[name="jw_icon"]').val(res.jwInfo.jw_icon);
                
                // 副标题
                $('#editJwModal input[name="jw_beizhu"]').val(res.jwInfo.jw_beizhu);
                
                // 后台的备注信息
                $('#editJwModal input[name="jw_beizhu_msg"]').val(res.jwInfo.jw_beizhu_msg);
                
                // 到期时间
                $('#editJwModal input[name="jw_expire_time"]').val(res.jwInfo.jw_expire_time);
                
                // 投放平台
                $('#editJwModal select[name="jw_platform"]').val(res.jwInfo.jw_platform);
                
                // 目标链接
                $('#editJwModal input[name="jw_url"]').val(res.jwInfo.jw_url);
                
                // ID
                $('#editJwModal input[name="jw_id"]').val(res.jwInfo.jw_id);
                
                // 2.4.1新增
                // 访问量限制
                if(res.user_admin == 1 || res.user_admin == '1') {
                    
                    // 超管才会显示
                    $('#editJwModal .adminShow').css('display','block');
                    $('#editJwModal input[name="jw_fwl_limit"]').val(res.jwInfo.jw_fwl_limit);
                    $('#editJwModal select[name="jw_status"]').val(res.jwInfo.jw_status);
                }else {
                    
                    // 否则移除元素
                    $('#editJwModal .adminShow').remove();
                }
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
                
                // 隐藏Modal
                setTimeout('hideModal("editJwModal")', 500);
                
                // 重新加载列表
                setTimeout('getJwList();', 500);
                
                // 显示操作结果
                setTimeout(function(){
                    showMessage(res.msg, 2000);
                },500)
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
function shareJw(jwid,qrtype){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "server/shareJw.php?jw_id="+jwid+"&qrtype="+qrtype,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 二维码链接
                if(qrtype == 1) {
                    
                    // 抖音落地页链接
                    let douyin_landpage_url = res.douyinQrcode;
                    let douyin_landpage_url_cleaned_1 = douyin_landpage_url.replace("https://link.wtturl.cn/?target=", "");
                    let douyin_landpage_url_cleaned_2 = douyin_landpage_url_cleaned_1.replace("%26", "&");
                    let douyin_landpage_url_cleaned_3 = douyin_landpage_url_cleaned_2.replace("%23", "#");

                    $("#qrURL").html('<span id="jw_'+jwid+'">' + douyin_landpage_url_cleaned_3 + '</span>');
                    
                    // 生成二维码
                    new QRCode(document.getElementById("shareQrcode"), res.douyinQrcode);
                    
                    // 扫码提示
                    $('#scan-tips').html('如需生成抖音卡片请使用 <span style="color:#f00;">iOS抖音APP</span> 扫码');
                }else {
                    
                    // 通用落地页
                    $("#qrURL").html('<span id="jw_'+jwid+'">' + res.commonQrcode + '</span>');
                    
                    // 生成二维码
                    new QRCode(document.getElementById("shareQrcode"), res.commonQrcode);
                    
                    // 扫码提示
                    $('#scan-tips').html('请使用微信、浏览器、QQ等APP扫码查看');
                }
                
                // 给切换二维码类型增加data-jwid属性
                $('.qrcode-toggle button').each(function() {
                    $(this).attr('data-jwid', jwid);
                });
                
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

// 搜索外链
function searchJw() {
    
    // 获取关键词
    const keyword = $('input[name="keyword"]').val();
    
    if(keyword) {
        
        // 请求服务器
        $.ajax({
            type: "POST",
            url: "server/searchJw.php?keyword="+keyword,
            success: function(res){
                
                // 表头
                var $thead_HTML = $(
                    '<tr>' +
                    '   <th>jwid</th>' +
                    '   <th>抖音卡片样式</th>' +
                    '   <th>备注</th>' +
                    '   <th>投放平台</th>' +
                    '   <th>目标地址</th>' +
                    '   <th>访问次数</th>' +
                    '   <th>按钮点击</th>' +
                    '   <th title="达到这个访问量页面就无法使用">访问量限制</th>' +
                    '   <th>创建/到期</th>' +
                    '   <th class="createUser-node">用户</th>' +
                    '   <th>操作</th>' +
                    '</tr>'
                );
                $("#right .data-list thead").html($thead_HTML);
                
                // 状态码为200代表有数据
                if(res.code == 200){
                    
                    // 初始化
                    initialize_getJwList();
                    
                    // 如果有数据
                    // 遍历数据
                    for (var i=0; i<res.jwList.length; i++) {
                        
                        // 主标题与副标题
                        var douyin_card_html = `
                            <span class="card-view">
                                <span class="card-cover">
                                    <img src="${res.jwList[i].jw_icon}" />
                                </span>
                                <span class="card-info">
                                    <span class="card-title">${res.jwList[i].jw_title}</span>
                                    <span class="card-desc">${res.jwList[i].jw_beizhu}</span>
                                </span>
                            </span>
                        `;
                        
                        // 创建时间和到期时间的处理
                        if(getExpirationStatus(res.jwList[i].jw_expire_time) == 2) {
                            
                            // 即将过期
                            var datetime_html = `
                                <span class="datetime-td">创建 ${res.jwList[i].jw_create_time}</span>
                                <span class="datetime-td-yello-text">即将到期 ${res.jwList[i].jw_expire_time}</span>
                            `;
                        }else if(getExpirationStatus(res.jwList[i].jw_expire_time) == 1) {
                            
                            // 正常样式
                            var datetime_html = `
                                <span class="datetime-td">创建 ${res.jwList[i].jw_create_time}</span>
                                <span class="datetime-td">到期 ${res.jwList[i].jw_expire_time}</span>
                            `;
                        }else {
                            
                            // 已到期
                            var datetime_html = `
                                <span class="datetime-td">创建 ${res.jwList[i].jw_create_time}</span>
                                <span class="datetime-td-red-text">已到期 ${res.jwList[i].jw_expire_time}</span>
                            `;
                        }
                        
                        // 到期计算
                        function getExpirationStatus(targetTime) {
                            
                            // 解析目标时间字符串
                            let targetDate = new Date(targetTime.replace(/-/g, '/')); // 兼容 iOS 解析
                            let now = new Date();
                        
                            // 计算提前 2 天的时间
                            let warningDate = new Date(targetDate);
                            warningDate.setDate(warningDate.getDate() - 2);
                        
                            if (now < warningDate) {
                                return 1;  // 当前时间 < 提前2天的时间
                            } else if (now >= warningDate && now < targetDate) {
                                return 2; // 进入提前2天的提醒期
                            } else {
                                return 3;  // 当前时间已经超过目标时间
                            }
                        }
                        
                        // 访问次数
                        var jw_pv = res.jwList[i].jw_pv;
                        
                        // 访问量限制
                        var jw_fwl_limit = res.jwList[i].jw_fwl_limit;
                        
                        // 点击次数
                        var jw_clickNum = res.jwList[i].jw_clickNum;
                        
                        // URLScheme
                        var jw_url = res.jwList[i].jw_url;
                        
                        // 后台的备注信息
                        var jw_beizhu_msg = res.jwList[i].jw_beizhu_msg ? res.jwList[i].jw_beizhu_msg : ' - ';
                        
                        // 投放平台
                        var jw_platform = res.jwList[i].jw_platform;
                        var platformMap = {
                            'douyin': '<img src="img/jw_platform_icon_douyin.png" width="20" /> 抖音',
                            'kuaishou': '<img src="img/jw_platform_icon_kuaishou.png" width="20" /> 快手',
                            'qq': '<img src="img/jw_platform_icon_qq.png" width="20" /> QQ',
                            'xhs': '<img src="img/jw_platform_icon_xhs.png" width="20" /> 小红书',
                            'sms': '<img src="img/jw_platform_icon_sms.png" width="20" /> 短信',
                            'weibo': '<img src="img/jw_platform_icon_weibo.png" width="20" /> 微博',
                            'zhihu': '<img src="img/jw_platform_icon_zhihu.png" width="20" /> 知乎',
                            'common': '<img src="img/jw_platform_icon_common.png" width="20" /> 通用'
                        };
                        var jw_platform_text = jw_platform ? (platformMap[jw_platform] || ' - ') : ' - ';
                        
                        // ID
                        var jw_id = res.jwList[i].jw_id;
                        
                        // 列表
                        var $tbody_HTML = $(
                            '<tr>' +
                            '   <td>'+res.jwList[i].jw_id+'</td>' +
                            '   <td>'+douyin_card_html+'</td>' +
                            '   <td>'+jw_beizhu_msg+'</td>' +
                            '   <td>'+jw_platform_text+'</td>' +
                            '   <td>' +
                            '       <a href="javascript:;" id="'+jw_url+'" class="jwurl-td" onclick="showJwUrl(this)">查看</a> | ' +
                            '       <a href="javascript:;" id="'+jw_url+'" class="jwurl-td" onclick="copyJwUrl(this)">复制</a>' +
                            '   </td>' +
                            '   <td>'+jw_pv+'</td>' +
                            '   <td>'+jw_clickNum+'</td>' +
                            '   <td title="达到这个访问量页面就无法使用">' + (jw_fwl_limit ? jw_fwl_limit : '不限制') + '</td>' +
                            '   <td>'+datetime_html+'</td>' +
                            '   <td class="createUser-node">' + res.jwList[i].jw_create_user + '</td>' +
                            '   <td>' + 
                            '       <span class="cz-tag" data-toggle="modal" data-target="#ShareJwModal" onclick="shareJw('+jw_id+',1)">分享</span>' +
                            '       <span class="cz-tag" data-toggle="modal" data-target="#editJwModal" onclick="getJwInfo('+jw_id+')">编辑</span>' +
                            '       <span class="cz-tag" data-toggle="modal" data-target="#delJwModal" onclick="askDelJw('+jw_id+')">删除</span>' +
                            '   </td>' +
                            '</tr>'
                        );
                        $("#right .data-list tbody").append($tbody_HTML);
                    }
                    
                    // 如果不是超管
                    if(res.user_admin.toString() !== '1') {
                        
                        // 移除创建人这一列
                        $('.createUser-node').remove();
                        
                        // 移除搜索框
                        $('.search-view').remove();
                    }else {
                        
                        // 展示搜索框
                        $('.search-view').css('display','block');
                    }
                    
                    // 分页组件
                    fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
                }else{
                    
                    // 非200状态码
                    showMessage(res.msg, 2000);
                }
          },
          error: function(){
            
            // 发生错误
            errorPage('data-list','getZjyList.php');
          },
        });
    }else {
        
        // 关键词为空
        showMessage('请输入关键词！', 1500);
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
                
                appendOptionsToSelect($("select[name='jw_common_landpage']"), res.yccymList);
                appendOptionsToSelect($("select[name='jw_douyin_landpage']"), res.yccymList);
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
function getSuCai(pageNum,fromPannel){
    
    // 初始化
    $('#suCaiKu .modal-body .sucai-view').empty('');
    
    // 关闭创建界面
    hideModal('createJwModal');
    
    // 关闭编辑界面
    hideModal('editJwModal');
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 将fromPannel的值设置到隐藏的表单中
    $('#suCaiKu input[name="upload_sucai_fromPannel"]').val(fromPannel);
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'createJwModal'){
        
        // 上一个面板是 createJwModal 
        // 渲染出来的关闭按钮是需要返回 createJwModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'createJwModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'editJwModal'){
        
        // 上一个面板是 editJwModal
        // 渲染出来的关闭按钮是需要返回 editJwModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'editJwModal\')">&times;</button>'
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
                    var clickFunction = "selectSucaiToForm(" + sucai_id + ", '" + fromPannel.trim() + "')";
                    
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
                suCaifenyeControl(pageNum,fromPannel,res.nextpage,res.prepage,res.allpage);
                
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
function selectSucaiToForm(sucai_id,fromPannel){
    
    $.ajax({
        type: "POST",
        url: "server/selectSucaiToForm.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将选择的素材设置到表单中
                $('input[name="jw_icon"]').val(res.suCaiUrl);
                
                // 预览图
                $('.cover-view').html('<img src="'+res.suCaiUrl+'" title="点击更换" />');

                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",500);
                
                // 打开fromPannel的Modal
                setTimeout("showModal('"+fromPannel+"')",800);
                
                // Modal超出高度允许滚动
                $('.modal-open .modal').css('overflow-y','auto');
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
function suCaifenyeControl(thisPage,fromPannel,nextPage,prePage,allPage){

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
        '       <button id="'+nextPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
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
        '       <button id="1_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
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
        '       <button id="1_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
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
    var fromPannel = FenyeData_parts[1]; // 来源
    
    // 获取该页列表
    getSuCai(pageNum,fromPannel);
}

// 素材库的界面关闭后
// 点击右上角X会返回上一步
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏suCaiKu面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个Modal
    if(fromPannel == 'createJwModal'){
        
        showModal('createJwModal')
    }else if(fromPannel == 'editJwModal'){

        showModal('editJwModal')
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
        $('#createJwModal input[name="jw_beizhu"]').val('');
        $('#createJwModal input[name="jw_xcx_appid"]').val('');
        $('#createJwModal input[name="jw_xcx_appsecret"]').val('');
        $('#createJwModal input[name="jw_xcx_path"]').val('');
        $('#createJwModal input[name="jw_xcx_query"]').val('');
        $('#createJwModal input[name="jw_caoliaoqrcode"]').val('');
        $('#createJwModal input[name="jw_jinshandoc"]').val('');
        $('#createJwModal input[name="jw_tencentdoc"]').val('');
        $('#createJwModal input[name="jw_workwxpan"]').val('');
        $('#createJwModal input[name="jw_csdnblog"]').val('');
        $('#createJwModal input[name="jw_xcx_urlscheme"]').val('');
        // $('#createJwModal select[name="jw_dxccym"]').empty(); 2.4.0下线这个
        
        // 新增的
        $('#createJwModal input[name="jw_qywx"]').val('');
        $('#createJwModal input[name="jw_h5page"]').val('');
        $('#createJwModal input[name="jw_qqgroup"]').val('');
        $('#createJwModal input[name="jw_qqfriend"]').val('');
        $('#createJwModal input[name="jw_txym"]').val('');
        
        // 2.4.0新增
        $('#createJwModal input[name="jw_txwj"]').val('');
        $('#createJwModal input[name="jw_zhaopin"]').val('');
        $('#createJwModal select[name="jw_common_landpage"]').empty();
        $('#createJwModal select[name="jw_douyin_landpage"]').empty();
        
        // 预览封面图初始化
        // 先创建元素
        $('.cover-view').html('<div class="cover-view"> + </div>');
        
        // 之后动态添加属性
        $('.cover-view').attr('data-action', 'getSuCai')
                        .attr('data-type', '1')
                        .attr('data-modal', 'createJwModal');
        
        $(document).ready(function () {
            
            // 先定义数据
            const platforms = [
                { id: "douyin", name: "抖音", icon: "img/jw_platform_icon_douyin.png" },
                { id: "kuaishou", name: "快手", icon: "img/jw_platform_icon_kuaishou.png" },
                { id: "xhs", name: "小红书", icon: "img/jw_platform_icon_xhs.png" },
                { id: "qq", name: "QQ", icon: "img/jw_platform_icon_qq.png" },
                { id: "sms", name: "短信", icon: "img/jw_platform_icon_sms.png" },
                { id: "weibo", name: "微博", icon: "img/jw_platform_icon_weibo.png" },
                { id: "zhihu", name: "知乎", icon: "img/jw_platform_icon_zhihu.png" },
                { id: "common", name: "通用", icon: "img/jw_platform_icon_common.png" }
            ];
        
            // 获取渲染容器
            const container = $(".platform-container");
        
            // 渲染 HTML
            container.html(platforms.map(platform => `
                <span class="toufang_Platform_tag not-select" data-jw_platform="${platform.id}">
                    <img src="${platform.icon}" />${platform.name}
                </span>
            `).join(''));
        
            // 让第一个元素默认选中
            let firstTag = $(".toufang_Platform_tag").first();
            firstTag.addClass("selected").removeClass("not-select");
            $("input[name='jw_platform']").val(firstTag.attr("data-jw_platform"));
        
            // **事件委托绑定 click 事件**
            $(document).on("click", ".toufang_Platform_tag", function () {
                $(".toufang_Platform_tag").removeClass("selected").addClass("not-select");
                $(this).addClass("selected").removeClass("not-select");
                let selectedPlatform = $(this).attr("data-jw_platform");
                $("input[name='jw_platform']").val(selectedPlatform);
            });
        });
        
        // 获取当前日期和时间
        var now = new Date();
        
        // 计算一个月后的日期
        var oneMonthLater = new Date(now);
        oneMonthLater.setMonth(now.getMonth() + 1);
        
        // 如果设置月份后日期超出了下个月的最大天数，会自动调整到下个月的最后一天
        // 例如，1月31日 + 1个月 = 2月28日（或29日，如果是闰年）
        
        // 格式化日期和时间
        var year = oneMonthLater.getFullYear();
        var month = (oneMonthLater.getMonth() + 1).toString().padStart(2, '0'); // 月份是从0开始的
        var day = oneMonthLater.getDate().toString().padStart(2, '0');
        var hours = oneMonthLater.getHours().toString().padStart(2, '0');
        var minutes = oneMonthLater.getMinutes().toString().padStart(2, '0');
        var seconds = oneMonthLater.getSeconds().toString().padStart(2, '0');
        
        // 生成日期时间字符串
        var dateTimeString = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
        
        // 设置时间日期选择器的值
        $('input[name="jw_expire_time"]').val(dateTimeString);
        
        // 隐藏提示
        hideResult();

    }else if(module == 'edit'){
        
        // 编辑的时候
        $('#editJwModal select[name="jw_common_landpage"]').empty();
        $('#editJwModal select[name="jw_douyin_landpage"]').empty();
        hideResult();
    }
}

// 怎么填？
function zmt() {
    window.open('https://viusosibp88.feishu.cn/docx/IvEgdpI5Foo1DwxLTxZcl2NbnVc');
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

// 顶部弹出的message
function showMessage(text, duration = 3000) {
    const container = document.getElementById('messageContainer');
    const message = document.createElement('div');
    message.className = 'message';
    message.textContent = text;
    container.appendChild(message);

    // 强制触发 reflow，然后添加 show 类
    requestAnimationFrame(() => {
      message.classList.add('show');
    });

    // 移除 message
    setTimeout(() => {
      message.classList.remove('show');
      setTimeout(() => {
        container.removeChild(message);
      }, 400);
    }, duration);
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