
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的ApiKey列表
        getApiKeyList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getApiKeyList(1);
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
                $('#accountInfo').html(
                    '<span class="user_name">'+res.user_name+'</span><a href="javascript:;" onclick="exitLogin();">退出</a>'
                );
                initialize_Login('login',res.user_admin);
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
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
        
        // 判断管理权限
        if(adminStatus == 2){
            
            // 隐藏button-view
            $('#button-view').css('display','none');
            
            // 显示loadding
            warningPage('没有管理权限')
        }
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取ApiKey列表
function getApiKeyList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getApiKeyList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getApiKeyList.php?p=" + pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getApiKeyList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>用户</th>' +
                '   <th>ApiKey</th>' +
                '   <th>ApiSecrete</th>' +
                '   <th>状态</th>' +
                '   <th>白名单IP</th>' +
                '   <th>创建时间</th>' +
                '   <th>到期时间</th>' +
                '   <th>请求配额</th>' +
                '   <th>请求次数</th>' +
                '   <th style="text-align:right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.apiKeyList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）ApiKey
                    var apikey = res.apiKeyList[i].apikey;
                    
                    // （3）白名单IP
                    var apikey_ip = res.apiKeyList[i].apikey_ip;
                    if(apikey_ip){
                        
                        // 白名单IP
                        var apikey_ip = apikey_ip;
                    }else{
                        
                        // 不限制
                        var apikey_ip = '不限制';
                    }
                    
                    // （4）创建时间
                    var apikey_creat_time = res.apiKeyList[i].apikey_creat_time;
                    
                    // （5）到期时间
                    var apikey_expire = res.apiKeyList[i].apikey_expire;
                    
                    // （6）请求配额
                    var apikey_quota = res.apiKeyList[i].apikey_quota;
                    
                    // （7）请求次数
                    var apikey_num = res.apiKeyList[i].apikey_num;
                    
                    // （8）ID
                    var apikey_id = res.apiKeyList[i].apikey_id;
                    
                    // （9）状态
                    var apikey_status = res.apiKeyList[i].apikey_status;
                    if(apikey_status == '1'){
                        
                        // 正常
                        var apikey_status = '<span>正常</span>';
                    }else{
                        
                        // 关闭
                        var apikey_status = '<span class="status_close">停用</span>';
                    }
                    
                    // （10）用户
                    var apikey_user = res.apiKeyList[i].apikey_user;
                    
                    // （11）apikey_secrete
                    var apikey_secrete = res.apiKeyList[i].apikey_secrete;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+apikey_user+'</td>' +
                        '   <td>'+apikey+'</td>' +
                        '   <td>'+apikey_secrete+'</td>' +
                        '   <td>'+apikey_status+'</td>' +
                        '   <td>'+apikey_ip+'</td>' +
                        '   <td>'+apikey_creat_time+'</td>' +
                        '   <td>'+apikey_expire+'</td>' +
                        '   <td>'+apikey_quota+'</td>' +
                        '   <td>'+apikey_num+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#EditApiKeyModal" onclick="getApiKeyInfo('+apikey_id+')">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#DelApiKeyModal" onclick="askDelApiKey('+apikey_id+')">删除</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>' +
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
                    var $ApiKeyFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye('+res.nextpage+');" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.allpage+');" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $ApiKeyFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye(1);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.prepage+');" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $ApiKeyFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye(1);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.prepage+');" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.nextpage+');" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.allpage+');" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }
                
                // 渲染分页控件
                $("#right .data-card .fenye").html($ApiKeyFenye_HTML);
                
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
        errorPage('data-list','getApiKeyList.php');
      },
    });
}

// 分页
function getFenye(pageNum){
    
    // 获取该页列表
    getApiKeyList(pageNum);
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

// 创建ApiKey
function creatApiKey(){
    
    $.ajax({
        type: "POST",
        url: "./createApiKey.php",
        data: $('#creatApiKey').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏creatApiKeyModal modal
                setTimeout('hideModal("creatApiKeyModal")', 500);
                
                // 重新加载ApiKey列表
                setTimeout('getApiKeyList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createApiKey.php');
        }
    });
}

// 询问是否要删除ApiKey
function askDelApiKey(apikey_id){
    
    // 将群id添加到button的delApiKey函数用于传参执行删除
    $('#DelApiKeyModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delApiKey('+apikey_id+');">确定删除</button>'
    )
}

// 删除ApiKey
function delApiKey(apikey_id){
    
    $.ajax({
        type: "GET",
        url: "./delApiKey.php?apikey_id="+apikey_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                // 隐藏Modal
                hideModal("DelApiKeyModal");
                
                // 重新加载短网址列表
                setTimeout('getApiKeyList()', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delApiKey.php');
        }
    });
}

// 获取ApiKey详情
function getApiKeyInfo(apikey_id){
    
    $.ajax({
        type: "GET",
        url: "./getApiKeyInfo.php?apikey_id="+apikey_id,
        success: function(res){

            if(res.code == 200){

                // （1）ApiSecrete
                $('#apikey_secrete_edit').val(res.apikeyInfo.apikey_secrete);
                
                // （2）IP白名单
                $('#apikey_ip_edit').val(res.apikeyInfo.apikey_ip);
                
                // （3）请求配额
                $('#apikey_quota_edit').val(res.apikeyInfo.apikey_quota);
                
                // （4）到期时间
                $('#apikey_expire_edit').val(res.apikeyInfo.apikey_expire.substring(0,res.apikeyInfo.apikey_expire.lastIndexOf(" ")));
                
                // （5）状态
                if(res.apikeyInfo.apikey_status == '1'){
                    
                    // 正常
                    $("#apikey_status_edit").html('<option value="1">正常</option><option value="2">停用</option>');
                }else{
                    
                    // 停用
                    $("#apikey_status_edit").html('<option value="2">停用</option><option value="1">正常</option>');
                }
                
                // （6）ApiKey
                $('#apikey_edit').val(res.apikeyInfo.apikey);
                
                // （7）apikey_id
                $('#apikey_id_edit').val(apikey_id);
                
                // （8）请求次数
                $('#apikey_num_edit').val(res.apikeyInfo.apikey_num);
       
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getApiKeyInfo.php');
        }
    });
}

// 编辑ApiKey
function editApiKey(){
    
    $.ajax({
        type: "POST",
        url: "./editApiKey.php",
        data: $('#editApiKey').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("EditApiKeyModal")', 500);
                
                // 重新加载ApiKey列表
                setTimeout('getApiKeyList();', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editApiKey.php');
        }
    });
}

// 查询ApiKey
function checkApiKey() {
    
    $.ajax({
        type: "POST",
        url: './checkApiKey.php',
        data: $('#checkApiKey').serialize(),
        success: function(res){
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>用户</th>' +
                '   <th>ApiKey</th>' +
                '   <th>ApiSecrete</th>' +
                '   <th>状态</th>' +
                '   <th>白名单IP</th>' +
                '   <th>创建时间</th>' +
                '   <th>到期时间</th>' +
                '   <th>请求配额</th>' +
                '   <th>请求次数</th>' +
                '   <th style="text-align:right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 200状态码
            if(res.code == 200){
                
                // （1）序号
                var xuhao = 1;
                
                // （2）ApiKey
                var apikey = res.apikeyInfo.apikey;
                
                // （3）白名单IP
                var apikey_ip = res.apikeyInfo.apikey_ip;
                if(apikey_ip){
                    
                    // 白名单IP
                    var apikey_ip = apikey_ip;
                }else{
                    
                    // 不限制
                    var apikey_ip = '不限制';
                }
                
                // （4）创建时间
                var apikey_creat_time = res.apikeyInfo.apikey_creat_time;
                
                // （5）到期时间
                var apikey_expire = res.apikeyInfo.apikey_expire;
                
                // （6）请求配额
                var apikey_quota = res.apikeyInfo.apikey_quota;
                
                // （7）请求次数
                var apikey_num = res.apikeyInfo.apikey_num;
                
                // （8）ID
                var apikey_id = res.apikeyInfo.apikey_id;
                
                // （9）状态
                var apikey_status = res.apikeyInfo.apikey_status;
                
                if(apikey_status == '1'){
                    
                    // 正常
                    var apikey_status = '<span>正常</span>';
                }else{
                    
                    // 关闭
                    var apikey_status = '<span class="status_close">停用</span>';
                }
                
                // （10）用户
                var apikey_user = res.apikeyInfo.apikey_user;
                
                // （11）apikey_secrete
                var apikey_secrete = res.apikeyInfo.apikey_secrete;
                
                // 列表
                var $tbody_HTML = $(
                    '<tr>' +
                    '   <td>'+xuhao+'</td>' +
                    '   <td>'+apikey_user+'</td>' +
                    '   <td>'+apikey+'</td>' +
                    '   <td>'+apikey_secrete+'</td>' +
                    '   <td>'+apikey_status+'</td>' +
                    '   <td>'+apikey_ip+'</td>' +
                    '   <td>'+apikey_creat_time+'</td>' +
                    '   <td>'+apikey_expire+'</td>' +
                    '   <td>'+apikey_quota+'</td>' +
                    '   <td>'+apikey_num+'</td>' +
                    '   <td class="dropdown-td">' +
                    '       <div class="dropdown">' +
                    '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                    '           <div class="dropdown-menu">' +
                    '               <span class="dropdown-item" data-toggle="modal" data-target="#EditApiKeyModal" onclick="getApiKeyInfo('+apikey_id+')">编辑</span>' +
                    '               <span class="dropdown-item" data-toggle="modal" data-target="#DelApiKeyModal" onclick="askDelApiKey('+apikey_id+')">删除</span>' +
                    '           </div>' +
                    '       </div>' +
                    '   </td>' +
                    '</tr>' +
                    '</tr>'
                );
                $("#right .data-list tbody").html($tbody_HTML);
                
                setTimeout('hideModal("checkApiKeyModal")', 300);
                
            }else{
                
                // 非200状态码
                showErrorResult(res.msg)
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','checkApiKey.php');
      },
    });
}

// 随机生成用户名
function randUserName(){
    
    $('#apikey_user').val(creatPageToken(8));
}

// 随机生成Apikey
function randApiKey(){
    
    $('#apikey_edit').val(creatPageToken(10));
}

// 随机生成ApiSecrete
function randApiSecrete(){
    
    $('#apikey_secrete_edit').val(creatPageToken(28));
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

// 顶部操作结果信息提示框
function showTopAlert(content){
    $('#topAlert').text(content);
    $('#topAlert').css('display','block');
    setTimeout('hideTopAlert()', 2500); // 2.5秒后自动关闭
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

// 初始化（getApiKeyList）
function initialize_getApiKeyList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（creatApiKey）
function initialize_creatApiKey(){
    $("#apikey_user").val('');
    $("#apikey_ip").val('');
    // 默认为1年后到期
    $("#apikey_expire").val((new Date().getFullYear()+1) + '-' + (new Date().getMonth()+1) + '-' + (new Date().getDate()));
    hideResult();
}

// 初始化（checkApiKey）
function initialize_checkApiKey(){
    $("#apikey_check").val('');
    hideResult();
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