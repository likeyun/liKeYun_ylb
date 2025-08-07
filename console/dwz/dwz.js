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
    
    // clipboard插件
    var clipboard = new ClipboardJS('#right .data-list tbody .copyLink');
    clipboard.on('success', function(e) {
        
        // 复制成功
        showNotification('已复制');
    });
    
    // 2025-02-12新增
    const style = document.createElement("style");
    style.innerHTML = `
        .cz-click {
            padding: 4px 8px;
            width: 43px;
            text-align: center;
            background: #eee;
            color: #666;
            font-size: 13px;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 3px;
        }
        .span-tag-1 {
            padding: 4px 8px;
            width: 43px;
            text-align: center;
            background: #eee;
            color: #666;
            font-size: 13px;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 3px;
        }
        .span-tag-2 {
            padding: 4px 8px;
            width: 43px;
            text-align: center;
            color: rgb(59,94,225);
            background: rgba(59,94,225,0.1);
            font-size: 13px;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 3px;
        }
    `;
    document.head.appendChild(style);

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
                '   <th style="text-align:left;">ID</th>' +
                '   <th>标题</th>' +
                '   <th>短网址</th>' +
                '   <th>访问限制</th>' +
                '   <th>创建时间</th>' +
                '   <th>总访问量</th>' +
                '   <th>今天访问量</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){

                // 遍历数据
                for (var i=0; i<res.dwzList.length; i++) {
                    
                    // 数据判断并处理
                    // ID
                    var dwz_id = res.dwzList[i].dwz_id;
                    
                    // （2）标题
                    var dwz_title = res.dwzList[i].dwz_title;
                    
                    // （3）状态
                    if(res.dwzList[i].dwz_status == '1'){
                        
                        // 正常
                        var dwz_status = 
                        '<span class="switch-on" onclick="changeDwzStatus('+dwz_id+');">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var dwz_status = 
                        '<span class="switch-off" onclick="changeDwzStatus('+dwz_id+');">'+
                        '<span class="press"></span>'+
                        '</span>';
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
                        
                        var dwz_type = '<span class="span-tag-1">不限制</span>';
                    }else if(dwz_type == 2){
                        
                        var dwz_type = '<span class="span-tag-2">仅限微信内访问</span>';
                    }else if(dwz_type == 3){
                        
                        var dwz_type = '<span class="span-tag-2">仅限iOS设备访问</span>';
                    }else if(dwz_type == 4){
                        
                        var dwz_type = '<span class="span-tag-2">仅限Android设备访问</span>';
                    }else if(dwz_type == 5){
                        
                        var dwz_type = '<span class="span-tag-2">仅限手机浏览器访问</span>';
                    }else if(dwz_type == 6){
                        
                        var dwz_type = '<span class="span-tag-2">仅限PC浏览器访问</span>';
                    }
                    
                    // 今天访问量
                    var dwz_today_pv = JSON.parse(res.dwzList[i].dwz_today_pv.toString()).pv;
                    var dwz_today_date = JSON.parse(res.dwzList[i].dwz_today_pv.toString()).date;
                    
                    // 获取日期
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const todayDate = `${year}-${month}-${day}`;
                    
                    if(dwz_today_date == todayDate){
                        
                        // 日期一致
                        // 显示今天的访问量
                        var dwz_pv_today = dwz_today_pv;
                    }else{
                        
                        // 日期不一致
                        // 显示0
                        var dwz_pv_today = 0;
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td style="text-align:left;">'+dwz_id+'</td>' +
                        '   <td>'+dwz_title+'</td>' +
                        '   <td>' +
                        '       <span id="dwz_'+dwz_id+'">'+dwz_dlym+'/'+dwz_key+'</span>' +
                        '       <span class="copyLink" data-clipboard-action="copy" data-clipboard-target="#dwz_'+dwz_id+'">' +
                        '           <img src="../../static/img/copyLink.png" title="复制链接" />' +
                        '       </span>' +
                        '   </td>' +
                        '   <td>'+dwz_type+'</td>' +
                        '   <td>'+dwz_creat_time+'</td>' +
                        '   <td>'+dwz_pv+'</td>' +
                        '   <td>'+dwz_pv_today+'</td>' +
                        '   <td>'+dwz_status+'</td>' +
                        '   <td style="text-align:right;">' + 
                        '       <span class="cz-click" data-toggle="modal" data-target="#EditDwzModal" onclick="getDwzInfo(this)" id="'+dwz_id+'">编辑</span>' +
                        '   <span class="cz-click" onclick="resetDwzPv('+dwz_id+')" title="重置访问量">重置</span>' +
                        '       <span class="cz-click" data-toggle="modal" data-target="#DelDwzModal" onclick="askDelDwz('+dwz_id+')">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                getDwzListFenyeComponent(res.page,res.nextpage,res.prepage,res.allpage);
                
                // 设置路由
                setRouter(res.page);
                
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
        errorPage('data-list','getDwzList.php');
        
        // 隐藏顶部的button
        $('#right .button-view').html('');
      },
    });
}

// 设置路由
function setRouter(pageNum){
    
    // 当前页码不等于1的时候
    if(pageNum !== 1){
        window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
    }
}

// 分页组件
function getDwzListFenyeComponent(thisPage,nextPage,prePage,allPage){
    
    // 分页
    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1且总页码=1
        // 无需显示分页控件
        $("#right .data-card .fenye").css("display","none");
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1且总页码>1
        // 代表还有下一页
        var $dwzFenye_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">' +
        '       <img src="../../static/img/nextPage.png" /></button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">' +
        '       <img src="../../static/img/lastPage.png" /></button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $dwzFenye_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getFenye(this);" title="第一页">' +
        '       <img src="../../static/img/firstPage.png" /></button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">' +
        '       <img src="../../static/img/prevPage.png" /></button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
    }else{
        
        // 显示所有组件
        var $dwzFenye_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getFenye(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($dwzFenye_HTML);
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
                showNotification(res.msg);
            }else{
                
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('changeDwzStatus.php发生错误！')
        }
    });
}

// 创建短网址
function createDwz(){
    
    $.ajax({
        type: "POST",
        url: "./createDwz.php",
        data: $('#createDwz').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("createDwzModal")', 500);
                
                // 重新加载短网址列表
                setTimeout('getDwzList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createDwz.php');
        }
    });
}

// 批量创建短网址
function createBatchDwz(){
    
    $.ajax({
        type: "POST",
        url: "./createBatchDwz.php",
        data: $('#createBatchDwz').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作成功
                showSuccessResult(res.msg);
                
                // 将生成结果的容器显示出来
                $('#createBatchDwzModal .createResult').css('display','block');
                
                // 清空生成结果的容器
                $('#createBatchDwzModal .dwzCreateResult').val('');
                
                // 遍历结果
                for (var i = 0; i < res.dwzList.length; i++) {
                    
                    // 将生成结果依次输出
                    $('#createBatchDwzModal .dwzCreateResult').append(res.dwzList[i]+'<br/>');
                }
                
                // 重新加载短网址列表
                setTimeout('getDwzList();', 1000);
                
                // 将创建按钮修改为关闭
                $('#createBatchDwzModal .btnnav').html(
                    '<button type="button" class="default-btn" data-dismiss="modal">关闭</button>'
                );
            }else{
                
                // 操作失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createBatchDwz.php');
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
            showErrorResultForphpfileName('editDwz.php');
        }
    });
}

// 询问是否要删除短网址
function askDelDwz(dwz_id){
    
    // 获取dwz_id
    $('#DelDwzModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delDwz('+dwz_id+');">确定删除</button>'
    )
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
                
                // 隐藏Modal
                hideModal("DelDwzModal");
                
                // 重新加载短网址列表
                setTimeout('getDwzList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delDwz.php');
        }
    });
}

// 获取短网址详情
function getDwzInfo(e){

    // 获取dwz_id
    var dwz_id = e.id;
    
    // 根据dwz_id获取详情
    $.ajax({
        type: "GET",
        url: "./getDwzInfo.php?dwz_id="+dwz_id,
        success: function(res){

            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // （1）标题
                $("#EditDwzModal input[name='dwz_title']").val(res.dwzInfo.dwz_title);
                
                // 获取域名列表
                getDomainNameList('edit')
                
                // （2）获取当前设置的域名
                $("#EditDwzModal select[name='dwz_rkym']").append(
                    '<option value="'+res.dwzInfo.dwz_rkym+'">'+res.dwzInfo.dwz_rkym+'</option>'
                );
                
                $("#EditDwzModal select[name='dwz_zzym']").append(
                    '<option value="'+res.dwzInfo.dwz_zzym+'">'+res.dwzInfo.dwz_zzym+'</option>'
                );
                
                $("#EditDwzModal select[name='dwz_dlym']").append(
                    '<option value="'+res.dwzInfo.dwz_dlym+'">'+res.dwzInfo.dwz_dlym+'</option>'
                );
                
                // （3）短网址状态
                if(res.dwzInfo.dwz_status == '1'){
                    
                    // 正常
                    $("#EditDwzModal select[name='dwz_status']").html(
                        '<option value="1">正常</option><option value="2">停用</option>'
                    );
                }else{
                    
                    // 停用
                    $("#EditDwzModal select[name='dwz_status']").html(
                        '<option value="2">停用</option><option value="1">正常</option>'
                    );
                }
                
                // （4）目标链接
                $("#EditDwzModal input[name='dwz_url']").val(res.dwzInfo.dwz_url);
                
                // （5）短网址Key
                $("#EditDwzModal input[name='dwz_key']").val(res.dwzInfo.dwz_key);
                
                // （6）访问限制
                var dwz_type = res.dwzInfo.dwz_type;
                    
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
                
                // （7）轮询域名
                if(res.dwzInfo.dwz_lxymStatus == '1'){
                    
                    // 启用
                    $("#EditDwzModal select[name='dwz_lxymStatus']").html(
                        '<option value="1">启用</option><option value="2">不启用</option>'
                    );
                }else{
                    
                    // 不启用
                    $("#EditDwzModal select[name='dwz_lxymStatus']").html(
                        '<option value="2">不启用</option><option value="1">启用</option>'
                    );
                }
                
                // 先将当前设置的访问限制加进去
                $("#EditDwzModal select[name='dwz_type']").html('<option value="'+res.dwzInfo.dwz_type+'">'+dwz_type+'</option>');
                
                // 再将可选的访问限制加进去
                var $dwz_type_edit_HTML = $(
                '<option value="1">不限制</option>' +
                '<option value="2">仅限微信内访问</option>' +
                '<option value="3">仅限iOS设备访问</option>' +
                '<option value="4">仅限Android设备访问</option>' +
                '<option value="5">仅限手机浏览器访问</option>' +
                '<option value="6">仅限PC浏览器访问</option>'
                );
                $("#EditDwzModal select[name='dwz_type']").append($dwz_type_edit_HTML);
                
                // dwz_id
                $("#EditDwzModal input[name='dwz_id']").val(dwz_id);
                            
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getDwzInfo.php');
        }
    });
}

// 查询短网址
function checkDwz() {
    
    // 获取关键词
    const keyword = $('input[name="dwz_keyword"]').val();
    
    if(!keyword){
        
        // 空值
        showNotification('请输入短网址标题关键词或Key');
        
        // 设置表单边框为红色
        $('input[name="dwz_keyword"]').css('border-color','#f00');
    }else{
        
        // 查询
        $.ajax({
            type: "POST",
            url: './checkDwz.php?keyword='+keyword,
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
                
                // 200状态码
                if(res.code == 200){
                    
                    // 初始化
                    $("#right .data-list tbody").empty('');
                    
                    // 隐藏分页
                    $('#right .fenye').css('display','none');
                    
                    for (var i=0; i<res.dwzList.length; i++) {
                        
                        // （1）序号
                        var xuhao = i+1;
                        
                        // （2）标题
                        var dwz_title = res.dwzList[i].dwz_title;
                        
                        // （3）状态
                        if(res.dwzList[i].dwz_status == '1'){
                            
                            // 正常
                            var dwz_status = '<span>正常</span>';
                        }else{
                            
                            // 关闭
                            var dwz_status = '<span class="status_close">停用</span>';
                        }
                        
                        // （4）创建时间
                        var dwz_creat_time = res.dwzList[i].dwz_creat_time;
                        
                        // （5）访问量
                        var dwz_pv = res.dwzList[i].dwz_pv;
                        
                        // （6）ID
                        var dwz_id = res.dwzList[i].dwz_id;
                        
                        // （7）短链域名
                        var dwz_dlym = res.dwzList[i].dwz_dlym;
                        
                        // （8）Key
                        var dwz_key = res.dwzList[i].dwz_key;
                        
                        // （9）访问限制
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
                        $("#right .data-list tbody").append($tbody_HTML);
                    }
                    
                    // 显示查询的结果
                    showNotification(res.msg);
                    
                }else{
                    
                    // 非200状态码
                    showNotification(res.msg);
                    
                    // 未登录
                    if(res.code == 201){
                        
                        // 跳转到登录页面
                        jumpUrl('../login/');
                    }
                }
                
          },
          error: function(){
            
            // 发生错误
            showNotification('checkDwz.php服务错误！');
          },
        });
        
        // 恢复表单样式
        $('input[name="dwz_keyword"]').css('border-color','#CED4DA');
    }
}

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
                appendOptionsToSelect($("select[name='dwz_rkym']"), res.rkymList);
                appendOptionsToSelect($("select[name='dwz_zzym']"), res.ldymList);
                appendOptionsToSelect($("select[name='dwz_dlym']"), res.dlymList);
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

// 重置短网址总PV
function resetDwzPv(dwz_id){
    
    $.ajax({
        type: "POST",
        url: "resetDwzPv.php?dwz_id=" + dwz_id,
        success: function(res){
            
            // 重置完成
            showNotification(res.msg);
            setTimeout('getDwzList()',500);
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！');
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

// 初始化（getdwzList获取短网址列表）
function initialize_getdwzList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}


// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 初始化
// 获取域名列表
function initialize_getDomainNameList(module){
    
    // 默认值
    $("#createDwzModal input[name='dwz_title']").val('');
    $("#createDwzModal input[name='dwz_url']").val('');
    $("select[name='dwz_rkym']").empty();
    $("select[name='dwz_zzym']").empty();
    $("select[name='dwz_dlym']").empty();
    
    var $dwz_dlws_HTML = $(
    '<option value="4">4位数</option>' +
    '<option value="5">5位数</option>' +
    '<option value="6">6位数</option>' +
    '<option value="7">7位数</option>'
    );
    $("select[name='dwz_dlws']").html($dwz_dlws_HTML);
    
    var $dwz_type_HTML = $(
    '<option value="1">不限制</option>' +
    '<option value="2">仅限微信内访问</option>' +
    '<option value="3">仅限iOS设备访问</option>' +
    '<option value="4">仅限Android设备访问</option>' +
    '<option value="5">仅限手机浏览器访问</option>' +
    '<option value="6">仅限PC浏览器访问</option>'
    );
    $("select[name='dwz_type']").html($dwz_type_HTML);
    
    // 清空目标链接输入框
    $("#createBatchDwzModal textarea[name='dwz_urls']").val('');
    
    // 隐藏生成结果并清空
    $("#createBatchDwzModal .createResult").css('display','none');
    $("#createBatchDwzModal .dwzCreateResult").html('');
    
    // 恢复创建按钮
    $('#createBatchDwzModal .btnnav').html(
        '<button type="button" class="default-btn" onclick="createBatchDwz()">立即创建</button>'
    );
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