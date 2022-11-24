
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的短网址数据列表
        getDwzList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getDwzList();
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
                $('#accountInfo').html('<span class="user_name">'+res.user_name+'</span><a href="javascript:;" onclick="exitLogin();">退出</a>');
                initialize_Login('login',res.user_admin)
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
                initialize_Login('unlogin');
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
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
            $('#openApi').html('<a href="./openApi.html"><button class="tint-btn" style="margin-left: 5px;">开放API</button></a>');
        }
        
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
        $('#openApi').css('display','none');
    }
}

// 获取短网址列表
function getDwzList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getDwzList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getDwzList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getdwzList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>标题</th>' +
                '   <th>短网址</th>' +
                '   <th>访问限制</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问量</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.dwzList.length; i++) {
                    
                    // 数据判断并处理
                    // ID
                    var dwz_id = res.dwzList[i].dwz_id;
                    
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）标题
                    var dwz_title = res.dwzList[i].dwz_title;
                    
                    // （3）状态
                    if(res.dwzList[i].dwz_status == '1'){
                        
                        // 正常
                        var dwz_status = '<span class="switch-on" onclick="changeDwzStatus('+dwz_id+');"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var dwz_status = '<span class="switch-off" onclick="changeDwzStatus('+dwz_id+');"><span class="press"></span></span>';
                    }
                    
                    // （4）创建时间
                    var dwz_creat_time = res.dwzList[i].dwz_creat_time;
                    
                    // （5）访问量
                    var dwz_pv = res.dwzList[i].dwz_pv;
                    
                    // （6）短链域名
                    var dwz_dlym = res.dwzList[i].dwz_dlym;
                    
                    // （7）Key
                    var dwz_key = res.dwzList[i].dwz_key;
                    
                    // （8）访问限制
                    var dwz_type = res.dwzList[i].dwz_type;
                    
                    if(dwz_type == 1){
                        
                        var dwz_type = '不限制';
                    }else if(dwz_type == 2){
                        
                        var dwz_type = '仅限微信内访问';
                    }else if(dwz_type == 3){
                        
                        var dwz_type = '仅限iOS设备访问';
                    }else if(dwz_type == 4){
                        
                        var dwz_type = '仅限Android设备访问';
                    }else if(dwz_type == 5){
                        
                        var dwz_type = '仅限手机浏览器访问';
                    }else if(dwz_type == 6){
                        
                        var dwz_type = '仅限PC浏览器访问';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+dwz_title+'</td>' +
                        '   <td>'+dwz_dlym+'/'+dwz_key+'</td>' +
                        '   <td>'+dwz_type+'</td>' +
                        '   <td>'+dwz_creat_time+'</td>' +
                        '   <td>'+dwz_pv+'</td>' +
                        '   <td>'+dwz_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#EditDwzModal" onclick="getDwzInfo(this)" id="'+dwz_id+'">编辑</a>' +
                        '               <a class="dropdown-item" href="javascript:;" id="'+dwz_id+'" data-toggle="modal" data-target="#DelDwzModal" onclick="askDelDwz(this)">删除</a>' +
                        '           </div>' +
                        '       </div>' +
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
                    var $dwzFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $dwzFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $dwzFenye_HTML = $(
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
                $("#right .data-card .fenye").html($dwzFenye_HTML);
                
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 非200状态码
                if(res.code == 205){
                    
                    // 205状态码代表用户升级版本但未初始化
                    warningPage('<p>检测到你正在升级版本</p><button onclick="Upgrade();" class="default-btn" style="cursor:pointer;">'+res.msg+'</button>');
                    $('#button-view').html('');
                    $('#openApi').html('');
                }else{
                    
                    warningPage(res.msg);
                }
                
                // 如果是未登录
                // 3秒后自动跳转到登录页面
                if(res.code == 201){
                    redirectLoginPage(3000);
                }
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
}

// 跳转到登录界面
function redirectLoginPage(second){
    
    // second毫秒后跳转
    setTimeout('location.href="../login/";', second);
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getDwzList(pageNum);
}

// 切换switch（changeDwzStatus）
function changeDwzStatus(dwz_id){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeDwzStatus.php?dwz_id="+dwz_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                getDwzList();
                showTopAlert(res.msg);
            }else{
                
                showTopAlert(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 升级
function Upgrade(){
    
    $.ajax({
        type: "POST",
        url: "./Upgrade.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                alert(res.msg);
                location.reload();
            }else{
                
                alert(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('服务器发生错误');
        }
    });
}

// 创建短网址
function creatDwz(){
    
    $.ajax({
        type: "POST",
        url: "./creatDwz.php",
        data: $('#creatDwz').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("creatDwzModal")', 500);
                
                // 重新加载短网址列表
                setTimeout('getDwzList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 编辑短网址
function editDwz(){
    
    $.ajax({
        type: "POST",
        url: "./editDwz.php",
        data: $('#editDwz').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏EditDwzModal modal
                setTimeout('hideModal("EditDwzModal")', 500);
                
                // 重新加载短网址列表
                setTimeout('getDwzList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 询问是否要删除短网址
function askDelDwz(e){
    
    // 获取dwz_id
    var dwz_id = e.id;
    
    // 将群id添加到button的delDwz函数用于传参执行删除
    $('#DelDwzModal .modal-footer').html('<button type="button" class="default-btn" onclick="delDwz('+dwz_id+');">确定删除</button>')
}

// 删除短网址
function delDwz(dwz_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delDwz.php?dwz_id="+dwz_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                // 隐藏modal
                hideModal("DelDwzModal");
                
                // 重新加载短网址列表
                setTimeout('getDwzList()', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 获取短网址详情
function getDwzInfo(e){

    // 获取dwz_id
    var dwz_id = e.id;
    
    // 根据dwz_id获取渠道码详情
    $.ajax({
        type: "GET",
        url: "./getDwzInfo.php?dwz_id="+dwz_id,
        success: function(res){

            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // （1）标题
                $('#dwz_title_edit').val(res.dwzInfo.dwz_title);
                
                // 获取域名列表
                getDomainNameList('edit')
                
                // （2）获取当前设置的域名
                $("#dwz_rkym_edit").append('<option value="'+res.dwzInfo.dwz_rkym+'">'+res.dwzInfo.dwz_rkym+'</option>');
                $("#dwz_zzym_edit").append('<option value="'+res.dwzInfo.dwz_zzym+'">'+res.dwzInfo.dwz_zzym+'</option>');
                $("#dwz_dlym_edit").append('<option value="'+res.dwzInfo.dwz_dlym+'">'+res.dwzInfo.dwz_dlym+'</option>');
                
                // （3）短网址状态
                if(res.dwzInfo.dwz_status == '1'){
                    
                    // 正常
                    $("#dwz_status_edit").html('<option value="1">正常</option><option value="2">停用</option>');
                }else{
                    
                    // 停用
                    $("#dwz_status_edit").html('<option value="2">停用</option><option value="1">正常</option>');
                }
                
                // （4）目标链接
                $('#dwz_url_edit').val(res.dwzInfo.dwz_url);
                
                // Android设备目标链接
                $('#dwz_android_url_edit').val(res.dwzInfo.dwz_android_url);
                
                // iOS设备目标链接
                $('#dwz_ios_url_edit').val(res.dwzInfo.dwz_ios_url);
                
                // Windows设备目标链接
                $('#dwz_windows_url_edit').val(res.dwzInfo.dwz_windows_url);
                
                // （5）短网址Key
                $('#dwz_key_edit').val(res.dwzInfo.dwz_key);
                
                // （6）访问限制
                // 先将目前设置的访问限制加进去
                var dwz_type = res.dwzInfo.dwz_type;
                
                // 先隐藏dwz_type_7_edit
                $('#dwz_type_7_edit').css('display','none');
                    
                if(dwz_type == 1){
                    
                    var dwz_type = '不限制';
                }else if(dwz_type == 2){
                    
                    var dwz_type = '仅限微信内访问';
                }else if(dwz_type == 3){
                    
                    var dwz_type = '仅限iOS设备访问';
                }else if(dwz_type == 4){
                    
                    var dwz_type = '仅限Android设备访问';
                }else if(dwz_type == 5){
                    
                    var dwz_type = '仅限手机浏览器访问';
                }else if(dwz_type == 6){
                    
                    var dwz_type = '仅限PC浏览器访问';
                }
                $("#dwz_type_edit").html('<option value="'+res.dwzInfo.dwz_type+'">'+dwz_type+'</option>');
                
                // 再将可选的访问限制加进去
                var $dwz_type_edit_HTML = $(
                '<option value="1">不限制</option>' +
                '<option value="2">仅限微信内访问</option>' +
                '<option value="3">仅限iOS设备访问</option>' +
                '<option value="4">仅限Android设备访问</option>' +
                '<option value="5">仅限手机浏览器访问</option>' +
                '<option value="6">仅限PC浏览器访问</option>'
                );
                $("#dwz_type_edit").append($dwz_type_edit_HTML);
                
                // dwz_id
                $('#dwz_id_edit').val(dwz_id);
                            
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 查询短网址
function checkDwz() {
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: './checkDwz.php',
        data: $('#checkDwz').serialize(),
        success: function(res){
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>标题</th>' +
                '   <th>短网址</th>' +
                '   <th>状态</th>' +
                '   <th>访问限制</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问量</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                // （1）序号
                var xuhao = 1;
                
                // （2）标题
                var dwz_title = res.dwzList.dwz_title;
                
                // （3）状态
                if(res.dwzList.dwz_status == '1'){
                    
                    // 正常
                    var dwz_status = '<span>正常</span>';
                }else{
                    
                    // 关闭
                    var dwz_status = '<span class="status_close">停用</span>';
                }
                
                // （4）创建时间
                var dwz_creat_time = res.dwzList.dwz_creat_time;
                
                // （5）访问量
                var dwz_pv = res.dwzList.dwz_pv;
                
                // （6）ID
                var dwz_id = res.dwzList.dwz_id;
                
                // （7）短链域名
                var dwz_dlym = res.dwzList.dwz_dlym;
                
                // （8）Key
                var dwz_key = res.dwzList.dwz_key;
                
                // （9）访问限制
                var dwz_type = res.dwzList.dwz_type;
                
                if(dwz_type == 1){
                    
                    var dwz_type = '不限制';
                }else if(dwz_type == 2){
                    
                    var dwz_type = '仅限微信内访问';
                }else if(dwz_type == 3){
                    
                    var dwz_type = '仅限iOS设备访问';
                }else if(dwz_type == 4){
                    
                    var dwz_type = '仅限Android设备访问';
                }else if(dwz_type == 5){
                    
                    var dwz_type = '仅限手机浏览器访问';
                }else if(dwz_type == 6){
                    
                    var dwz_type = '仅限PC浏览器访问';
                }
                
                // 列表
                var $tbody_HTML = $(
                    '<tr>' +
                    '   <td>'+xuhao+'</td>' +
                    '   <td>'+dwz_title+'</td>' +
                    '   <td>'+dwz_dlym+'/'+dwz_key+'</td>' +
                    '   <td>'+dwz_status+'</td>' +
                    '   <td>'+dwz_type+'</td>' +
                    '   <td>'+dwz_creat_time+'</td>' +
                    '   <td>'+dwz_pv+'</td>' +
                    '   <td class="dropdown-td">' +
                    '       <div class="dropdown">' +
                    '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                    '           <div class="dropdown-menu">' +
                    '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#EditDwzModal" onclick="getDwzInfo(this)" id="'+dwz_id+'">编辑</a>' +
                    '               <a class="dropdown-item" href="javascript:;" id="'+dwz_id+'" data-toggle="modal" data-target="#DelDwzModal" onclick="askDelDwz(this)">删除</a>' +
                    '           </div>' +
                    '       </div>' +
                    '   </td>' +
                    '</tr>'
                );
                $("#right .data-list tbody").html($tbody_HTML);
                
                // 0.5秒后自动关闭
                setTimeout('hideModal("checkDwzModal");',500);
                
                // 清空输入框
                setTimeout('$("#dwz_check").val("");',600);
                
            }else{
                
                // 非200状态码
                showErrorResult(res.msg);
                
                // 如果是未登录
                // 3秒后自动跳转到登录页面
                if(res.code == 201){
                    redirectLoginPage(3000);
                }
                
                // 清空输入框
                setTimeout('$("#dwz_check").val("");',2500);
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
}

// 获取域名列表
function getDomainNameList(module){
    
    // 判断是作用于哪个模块的
    if(module == 'creat'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "./getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // 操作反馈（操作成功）
                    // 判断rkymList是否有域名
                    if(res.rkymList.length>0){;
                        for (var i=0; i<res.rkymList.length; i++) {
                            $("#dwz_rkym").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#dwz_rkym").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#dwz_zzym").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#dwz_zzym").append('<option value="">暂无中转域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#dwz_dlym").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#dwz_dlym").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
            }
        });
    }else if(module == 'edit'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "./getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // 操作反馈（操作成功）
                    // 判断rkymList是否有域名
                    if(res.rkymList.length>0){;
                        for (var i=0; i<res.rkymList.length; i++) {
                            $("#dwz_rkym_edit").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#dwz_rkym_edit").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#dwz_zzym_edit").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#dwz_zzym_edit").append('<option value="">暂无中转域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#dwz_dlym_edit").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#dwz_dlym_edit").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
            }
        });
    }
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
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
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

// 错误页面
function errorPage(text){
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/errorIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
}

// 提醒页面
function warningPage(text){
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/warningIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
}

// 初始化（getdwzList获取短网址列表）
function initialize_getdwzList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'creat'){
        
        // 将所有值清空
        $("#dwz_title").val('');
        $("#dwz_url").val('');
        $("#dwz_rkym").empty();
        $("#dwz_zzym").empty();
        $("#dwz_dlym").empty();
        $("#dwz_dlws").empty();
        $("#dwz_type").empty();
        hideResult();
        
        // 设置默认值
        $("#dwz_rkym").append('<option value="">选择入口域名</option>');
        $("#dwz_zzym").append('<option value="">选择中转域名</option>');
        $("#dwz_dlym").append('<option value="">选择短链域名</option>');
        
        var $dwz_dlws_HTML = $(
        '<option value="4">4位数</option>' +
        '<option value="5">5位数</option>' +
        '<option value="6">6位数</option>' +
        '<option value="7">7位数</option>'
        );
        $("#dwz_dlws").html($dwz_dlws_HTML);
        
        var $dwz_type_HTML = $(
        '<option value="1">不限制</option>' +
        '<option value="2">仅限微信内访问</option>' +
        '<option value="3">仅限iOS设备访问</option>' +
        '<option value="4">仅限Android设备访问</option>' +
        '<option value="5">仅限手机浏览器访问</option>' +
        '<option value="6">仅限PC浏览器访问</option>'
        );
        $("#dwz_type").html($dwz_type_HTML);
    }else if(module == 'edit'){
        
        // 将所有值清空
        $("#dwz_rkym_edit").empty();
        $("#dwz_zzym_edit").empty();
        $("#dwz_dlym_edit").empty();
        hideResult();
    }

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