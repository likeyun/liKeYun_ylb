
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

// 解析文案
function jiexiWenan() {
    
    let text = $('#createZjyModal textarea[name="tblm_wenan"]').val();
    if(!text) {
        showErrorResult('请粘贴文案...')
        return;
    }
    if(!text.includes("到手价") && !text.includes("券面额")) {
        showErrorResult('文案不符合规则')
        return;
    }
    
    $('#createZjyModal .action-btn').text('正在解析...');
    $('#createZjyModal .action-btn').attr('onclick', '');
    text = text.replace(/\s+/g, ' ').trim();
    text = removeEmojis(text);
    const parseText = (text) => {
        
        const titleMatch = text.match(/^(.*?)【推荐理由】/);
        const quanhoujiaMatch = text.match(/【到手价】\s*(\d+\.?\d*)\s*元/);
        const quanmianjiaMatch = text.match(/【券面额】\s*(\d+\.?\d*)\s*元/);
        if (titleMatch && quanhoujiaMatch && quanmianjiaMatch) {
            const title = titleMatch[1].trim();
            const quanhoujia = parseFloat(quanhoujiaMatch[1]);
            const quanmianjia = parseFloat(quanmianjiaMatch[1]);
            const yuanjia = quanhoujia + quanmianjia;
            const short_title = safeSubstring(title, 0, 18);
            let target = "【下单链接】";
            let startIndex = text.indexOf(target) + target.length;
            let result = text.substring(startIndex);
            const taokouling = result.replace(/https?:\/\/[^\s]+/g, '');

            // 填写到表单
            $('#createZjyModal input[name="zjy_long_title"]').val(title);
            $('#createZjyModal input[name="zjy_short_title"]').val(short_title);
            $('#createZjyModal input[name="zjy_original_cost"]').val(yuanjia);
            $('#createZjyModal input[name="zjy_discounted_price"]').val(quanhoujia);
            $('#createZjyModal input[name="zjy_tkl"]').val(taokouling);
            
            return { title, quanhoujia, quanmianjia, yuanjia, taokouling };
        } else {
            return "解析失败";
            showErrorResult('解析失败，建议手动输入');
            $('#createZjyModal .action-btn').html('<span>重试</span>');
        }
    };
    
    const getParsedResult = (inputText) => {
        return parseText(inputText);
    };
    
    if(getParsedResult(text) !== "解析失败") {
        setTimeout(function(){
            $('#createZjyModal .explain_result').css('display','block');
            showSuccessResult('解析成功');
            $('#createZjyModal .action-btn').text('立即创建');
            $('#createZjyModal .action-btn').attr('onclick', 'createZjy()');
        },1500)
    }
        
    console.log(getParsedResult(text));
}

// 截取字符
function safeSubstring(str, start, length) {
    let result = '';
    let count = 0;
    for (let i = start; i < str.length; i++) {
        let char = str.charAt(i);
        if (char.match(/[\u4e00-\u9fa5]/)) {
            count += 2;
        } else {
            count += 1;
        }
        if (count > length) break;
        result += char;
    }
    return result;
}

// 使用正则表达式匹配所有emoji字符
function removeEmojis(str) {
  return str.replace(/[\uD83C-\uDBFF\uDC00-\uDFFF]+/g, ' ');
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
                
                // 隐藏Modal
                setTimeout('hideModal("createZjyModal")', 500);
                setTimeout('hideModal("createZjyQuickModal")', 500);
                
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

// 一键创建
function createZjyQuick(){
    
    $.ajax({
        type: "POST",
        url: "./createZjy.php",
        data: $('#createZjyQuick').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("createZjyModal")', 500);
                setTimeout('hideModal("createZjyQuickModal")', 500);
                
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
                
                // 长标题
                $('#EditZjyModal input[name="zjy_long_title"]').val(res.zjyInfo.zjy_long_title);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // 获取当前设置的域名
                $('#EditZjyModal select[name="zjy_rkym"]').append(
                    '<option value="'+res.zjyInfo.zjy_rkym+'">'+res.zjyInfo.zjy_rkym+'</option>'
                );
                
                $('#EditZjyModal select[name="zjy_ldym"]').append(
                    '<option value="'+res.zjyInfo.zjy_ldym+'">'+res.zjyInfo.zjy_ldym+'</option>'
                );
                
                $('#EditZjyModal select[name="zjy_dlym"]').append(
                    '<option value="'+res.zjyInfo.zjy_dlym+'">'+res.zjyInfo.zjy_dlym+'</option>'
                );
                
                // 短标题
                $('#EditZjyModal input[name="zjy_short_title"]').val(res.zjyInfo.zjy_short_title);
                
                // 淘口令
                $('#EditZjyModal input[name="zjy_tkl"]').val(res.zjyInfo.zjy_tkl);
                
                // 原价
                $('#EditZjyModal input[name="zjy_original_cost"]').val(res.zjyInfo.zjy_original_cost);
            
                // 券后价
                $('#EditZjyModal input[name="zjy_discounted_price"]').val(res.zjyInfo.zjy_discounted_price);
                
                // 商品主图
                $('#EditZjyModal input[name="zjy_goods_img"]').val(res.zjyInfo.zjy_goods_img);
                
                // 商品链接
                $('#EditZjyModal input[name="zjy_goods_link"]').val(res.zjyInfo.zjy_goods_link);
                
                // zjy_id
                $('#EditZjyModal input[name="zjy_id"]').val(zjy_id);
                            
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
                
                $('#configZjy input[name="zjy_app_key"]').val(res.zjyConfigInfo.zjy_config_appkey);
                $('#configZjy input[name="zjy_app_scret"]').val(res.zjyConfigInfo.zjy_config_sid);
                $('#configZjy input[name="zjy_pid"]').val(res.zjyConfigInfo.zjy_config_pid);
                $('#configZjy input[name="zjy_tbname"]').val(res.zjyConfigInfo.zjy_config_tbname);
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

// 上传
document.addEventListener('DOMContentLoaded', function() {
    
    // 选择本地商品主图（创建时）
    $("#chooseIMG_create").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("createZjy"));
            
            // 上传
            uploadZhutu(imageData,"createZjyModal");
        }
        
    });
    
    // 选择本地商品主图（编辑时）
    $("#chooseIMG_edit").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("editZjy"));
            
            // 上传
            uploadZhutu(imageData,"EditZjyModal");
        }
        
    });
    
    // 清除file的选择
    $('#chooseIMG_create').val('');
    $('#chooseIMG_edit').val('');
    
    // 上传
    function uploadZhutu(imageData,modalId){
        
        $.ajax({
            url: "../upload.php",
            type: "POST",
            data: imageData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(res) {
                
                if(res.code == 200){
                    
                    // 上传成功
                    $('#'+modalId+' input[name="zjy_goods_img"]').val(res.url);
                    $('#'+modalId+' .uploadText').text("重新上传");
                    showSuccessResult(res.msg);
                }else{
                    
                    // 上传失败
                    showErrorResult(res.msg);
                }
            },
            error: function() {
                
                // 上传失败
                showErrorResultForphpfileName('upload.php');
            }
        });
    }
    
    // 上传至素材库
    $("#uploadSuCaiTosuCaiKu").change(function(e){
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        if(fileSelect.length>0){
            
            // file表单数据
            var imageData = new FormData(document.getElementById("uploadSuCaiTosuCaiKuForm"));
            
            // 获取fromPannel
            var fromPannel = $('#uploadSuCai_fromPannel').val();
            
            // 异步上传
            $.ajax({
                url:"../public/uploadToSuCaiKu.php",
                type:"POST",
                data:imageData,
                cache: false,
                processData: false,
                contentType: false,
                success: function(res) {
                    
                    if(res.code == 200){
                        
                        // 上传成功
                        // 刷新素材库
                        getSuCai(1,fromPannel);
                    }else{
                        
                        // 上传失败
                        showErrorResult(res.msg)
                    }
                    
                    // 清空file控件的选择
                    $('#uploadSuCaiTosuCaiKu').val('');
                },
                error: function() {
                    
                    // 上传失败
                    showErrorResultForphpfileName('uploadToSuCaiKu.php');
                }
            })
        }
    })
})

// 获取素材
function getSuCai(pageNum,fromPannel){
    
    // 初始化
    $('#suCaiKu .modal-body .sucai-view').empty('');
    
    // 关闭界面
    hideModal('createZjyModal');
    
    // 关闭编辑单页界面
    hideModal('EditZjyModal');
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 如果fromPannel是EditZjyModal
    // 需要修改素材库底部的取消按钮的点击事件函数为EditZjyModal
    if(fromPannel == 'EditZjyModal'){
        
        $('#suCaiKu .modal-footer .btnnav').html(
            '<button type="button" class="default-btn" data-dismiss="modal" onclick="hideSuCaiPannel(\'EditZjyModal\')">取消</button>'
        );
        
        // 还需要修改素材库uploadSuCai_fromPannel的value
        // 不修改的话，会造成在选择素材的页面点击上传素材
        // 上传后的素材，点击选择素材后会打开创建单页而不是但会继续编辑
        $('#uploadSuCai_fromPannel').val('EditZjyModal');
    }
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'createZjyModal'){
        
        // 上一个面板是createZjyModal
        // 渲染出来的关闭按钮是需要返回createZjyModal的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'createZjyModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'EditZjyModal'){
        
        // 上一个面板是EditZjyModal
        // 渲染出来的关闭按钮是需要返回EditZjyModal的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'EditZjyModal\')">&times;</button>'
        );
    }
    
    // 开始获取素材列表
    $.ajax({
        type: "POST",
        url: "../public/getSuCaiList.php?p="+pageNum,
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
                    
                    // 根据fromPannel决定点击事件
                    if(fromPannel == 'createZjyModal'){
                        
                        // 更新
                        var clickFunction = 'selectSucaiForZhuitu('+sucai_id+')';
                        
                    }else if(fromPannel == 'EditZjyModal'){
                        
                        // 新增
                        var clickFunction = 'selectSucaiEditZhuitu('+sucai_id+')';
                    }
                    
                    
                    var $sucaiList_HTML = $(
                    '<div class="sucai_msg" title="'+sucai_beizhu+'" onclick="'+clickFunction+'">' +
                    '   <div class="sucai_cover">' +
                    '       <img src="../upload/'+sucai_filename+'" />' +
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
        '   <img src="../../static/img/noRes.png" class="noRes"/>' +
        '   <br/><p>'+text+'</p>'+
        '</div>'
    );
}

// 素材库分页控件
function suCaifenyeControl(thisPage,fromPannel,nextPage,prePage,allPage){

    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1 且 总页码=1
        // 无需显示分页控件
        $('#suCaiKu .fenye').css('display','none');
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1 且 总页码>1
        // 代表还有下一页
        // 需要显示下一页、最后一页控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#suCaiKu .fenye').css('display','block');
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        // 需要显示第一页、上一页控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#suCaiKu .fenye').css('display','block');
        
    }else{
        
        // 其他情况
        // 需要显示所有控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#suCaiKu .fenye').css('display','block');
    }
    
    // 渲染分页控件
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

// 选择当前素材（创建时）
function selectSucaiForZhuitu(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiForZhuitu.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将主图Url设置到表单中
                $("#createZjyModal input[name='zjy_goods_img']").val(res.zhutuImgUrl);
                
                // 修改从素材库选择素材的按钮文字
                $('#createZjyModal .selectText').text('重新选择素材');
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                showSuccessResultTimes('已选择',1100);
                
                // 打开createZjyModal
                setTimeout("showModal('createZjyModal')",1200);
                
                // 解决一个bug（这里别动）
                setTimeout("$('body').attr('class', 'modal-open')",1600);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiForZhuitu.php');
        }
    });
}

// 选择当前素材（编辑时）
function selectSucaiEditZhuitu(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiForZhuitu.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将主图Url设置到表单中
                $("#EditZjyModal input[name='zjy_goods_img']").val(res.zhutuImgUrl);
                
                // 修改从素材库选择素材的按钮文字
                $('#EditZjyModal .selectText').text('重新选择素材');
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                showSuccessResultTimes('已选择',1100);
                
                // 打开EditZjyModal
                setTimeout("showModal('EditZjyModal')",1200);
                
                // 解决一个bug（这里别动）
                setTimeout("$('body').attr('class', 'modal-open')",1600);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiEditZhuitu.php');
        }
    });
}

// 为了便于继续操作创建单页
// 素材库的界面关闭后
// 点击右上角X会继续打开创建单页
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏suCaiKu面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个 Modal
    if(fromPannel == 'createZjyModal'){
        
        // 显示createZjyModal
        showModal('createZjyModal')
    }else if(fromPannel == 'EditZjyModal'){
        
        // 显示EditZjyModal
        showModal('EditZjyModal')
    }
}

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times);
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
        $('#createZjyModal textarea[name="tblm_wenan"]').val('');
        $('#createZjyModal .action-btn').text('解析文案');
        $('#createZjyModal .action-btn').attr('onclick', 'jiexiWenan()');
        $('#createZjyModal .explain_result').css('display','none');
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