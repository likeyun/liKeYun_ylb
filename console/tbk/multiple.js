
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    // 根据页码获取数据
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的单页数据列表
        getMultiSPAList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getMultiSPAList();
    }
    
    // clipboard插件
    var clipboard = new ClipboardJS('#shareMultiSPAModal .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#shareMultiSPAModal .modal-footer button').text('已复制');
    });
    
    // 监听multi_project可编辑的DIV的输入
    $("#createSpaModal .multi_project").on("input", function() {
        
        // 实时获取输入的内容
        const multi_project_content = $(this).val();
        
        // 将内容实时加入到表单输入框
        $('#createSpaModal input[name="multiSPA_project"]').val(multi_project_content.replace(/\n/g, "<br/>"));
    });
    
    $("#editSpaModal .multi_project").on("input", function() {
        
        // 实时获取输入的内容
        const multi_project_content = $(this).val();
        
        // 将内容实时加入到表单输入框
        $('#editSpaModal input[name="multiSPA_project"]').val(multi_project_content.replace(/\n/g, "<br/>"));
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
        
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取单页列表
function getMultiSPAList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getMultiSPAList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getMultiSPAList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getMultiSPAList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>标题</th>' +
                '   <th>短链接</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问量</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 200状态码
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.multiSPAList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // 标题
                    var multiSPA_title = res.multiSPAList[i].multiSPA_title;
                    
                    // 创建时间
                    var multiSPA_addtime = res.multiSPAList[i].multiSPA_addtime;
                    
                    // 短链域名
                    var multiSPA_dlym = res.multiSPAList[i].multiSPA_dlym;
                    
                    // Key
                    var multiSPA_key = res.multiSPAList[i].multiSPA_key;
                    
                    // 访问量
                    var multiSPA_pv = res.multiSPAList[i].multiSPA_pv;
                    
                    // ID
                    var multiSPA_id = res.multiSPAList[i].multiSPA_id;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+multiSPA_title+'</td>' +
                        '   <td>'+multiSPA_dlym+'/s/'+multiSPA_key+'</td>' +
                        '   <td>'+multiSPA_addtime+'</td>' +
                        '   <td>'+multiSPA_pv+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#shareMultiSPAModal" onclick="shareMultiSPA('+multiSPA_id+')">分享</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editSpaModal" onclick="getMultiSPAInfo('+multiSPA_id+')">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#delMultiSPAModal" onclick="askDelMultiSPA('+multiSPA_id+')">删除</span>' +
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
        errorPage('data-list','getMultiSPAList.php');
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
        var $MultiSPAFenye_HTML = $(
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
        var $MultiSPAFenye_HTML = $(
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
        
        var $MultiSPAFenye_HTML = $(
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
    $("#right .data-card .fenye").html($MultiSPAFenye_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getMultiSPAList(pageNum);
}

// 创建单页
function createMultiSPA(){
    
    $.ajax({
        type: "POST",
        url: "./createMultiSPA.php",
        data: $('#createMultiSPA').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 创建成功
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createSpaModal")', 500);
                
                // 刷新列表
                setTimeout('getMultiSPAList()', 700);

            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createMultiSPA.php');
        }
    });
    
}

// 编辑单页
function editMultiSPA(){
    
    $.ajax({
        type: "POST",
        url: "./editMultiSPA.php",
        data: $('#editMultiSPA').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg);
                
                // 隐藏editSpaModal
                setTimeout('hideModal("editSpaModal")', 500);
                
                // 重新加载单页列表
                setTimeout('getMultiSPAList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editMultiSPA.php');
        }
    });
}

// 删除询问
function askDelMultiSPA(MultiSPA_id){
    
    // 将MultiSPA_id添加到button
    $('#delMultiSPAModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delMultiSPA('+MultiSPA_id+');">确定删除</button>'
    )
}

// 删除单页
function delMultiSPA(MultiSPA_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delMultiSPA.php?MultiSPA_id="+MultiSPA_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delMultiSPAModal");
                
                // 重新加载单页列表
                setTimeout('getMultiSPAList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delMultiSPA.php');
        }
    });
}

// 获取单页详情
function getMultiSPAInfo(multiSPA_id){
    
    $.ajax({
        type: "GET",
        url: "./getMultiSPAInfo.php?multiSPA_id="+multiSPA_id,
        success: function(res){

            if(res.code == 200){
                
                // 标题
                $('input[name="multiSPA_title"]').val(res.multiSPAInfo.multiSPA_title);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // 获取当前设置的域名
                $('select[name="multiSPA_rkym"]').append(
                    '<option value="'+res.multiSPAInfo.multiSPA_rkym+'">'+res.multiSPAInfo.multiSPA_rkym+'</option>'
                );
                
                $('select[name="multiSPA_ldym"]').append(
                    '<option value="'+res.multiSPAInfo.multiSPA_ldym+'">'+res.multiSPAInfo.multiSPA_ldym+'</option>'
                );
                
                $('select[name="multiSPA_dlym"]').append(
                    '<option value="'+res.multiSPAInfo.multiSPA_dlym+'">'+res.multiSPAInfo.multiSPA_dlym+'</option>'
                );
                
                // 商品主图预览
                if(res.multiSPAInfo.multiSPA_img){
                    
                    // 展示图片
                    $('#editSpaModal .goodsPic_preview').html(
                        '<a href="'+res.multiSPAInfo.multiSPA_img+'" target="blank" title="点击图片查看大图">' +
                        '<img src="'+res.multiSPAInfo.multiSPA_img+'" /></a>'
                    );
                }else{
                    
                    // 恢复默认
                    $('#editSpaModal .goodsPic_upload_preview .goodsPic_preview').html(
                        '<div class="upload_tips" title="无需主图可不上传">主图预览</div>'
                    );
                }
                
                // 渲染项目内容
                $('#editSpaModal .multi_project').val(res.multiSPAInfo.multiSPA_project.replace(/<br\/>/g, "\n"));
                                
                // multiSPA_project
                $('input[name="multiSPA_project"]').val(res.multiSPAInfo.multiSPA_project);
                
                // multiSPA_id
                $('input[name="multiSPA_id"]').val(res.multiSPAInfo.multiSPA_id);
                
                // multiSPA_img
                $('input[name="multiSPA_img"]').val(res.multiSPAInfo.multiSPA_img);
                            
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getMultiSPAInfo.php');
        }
    });
}

// 获取域名列表
function getDomainNameList(module){
    
    // 创建
    if(module == 'create'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "../public/getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // rkymList有域名
                    if(res.rkymList.length>0){
                        
                        for (var i=0; i<res.rkymList.length; i++) {
                            
                            // 将入口域名列表加到表单中
                            $("select[name='multiSPA_rkym']").append(
                                '<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='multiSPA_rkym']").append('<option value="">暂无入口域名</option>');
                    }
                    
                    // ldymList有域名
                    if(res.ldymList.length>0){
                        
                        for (var i=0; i<res.ldymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='multiSPA_ldym']").append(
                                '<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='multiSPA_ldym']").append('<option value="">暂无落地域名</option>');
                    }
                    
                    // dlymList有域名
                    if(res.dlymList.length>0){
                        
                        for (var i=0; i<res.dlymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='multiSPA_dlym']").append(
                                '<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='multiSPA_dlym']").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResultForphpfileName('getDomainNameList.php');
            }
        });
    }else if(module == 'edit'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "../public/getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // rkymList有域名
                    if(res.rkymList.length>0){
                        
                        for (var i=0; i<res.rkymList.length; i++) {
                            
                            // 将入口域名列表加到表单中
                            $("select[name='multiSPA_rkym']").append(
                                '<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='multiSPA_rkym']").append('<option value="">暂无入口域名</option>');
                    }
                    
                    // ldymList有域名
                    if(res.ldymList.length>0){
                        
                        for (var i=0; i<res.ldymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='multiSPA_ldym']").append(
                                '<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='multiSPA_ldym']").append('<option value="">暂无落地域名</option>');
                    }
                    
                    // dlymList有域名
                    if(res.dlymList.length>0){
                        
                        for (var i=0; i<res.dlymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='multiSPA_dlym']").append(
                                '<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='multiSPA_dlym']").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResultForphpfileName('getDomainNameList.php');
            }
        });
    }
}

// 分享单页
function shareMultiSPA(MultiSPA_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    // 分享
    $.ajax({
        type: "GET",
        url: "./shareMultiSPA.php?MultiSPA_id="+MultiSPA_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").text(res.longUrl);
                
                // 短链接
                $("#shortUrl").html('<span id="MultiSPA_'+MultiSPA_id+'">'+res.shortUrl+'</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.longUrl);
                
                // 复制按钮
                $('#shareMultiSPAModal .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#MultiSPA_'+MultiSPA_id+'">复制链接</button>'
                );
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareMultiSPA.php');
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

// 上传
document.addEventListener('DOMContentLoaded', function() {
    
    // 上传商品主图（创建时）
    $("#selectZhutu").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("createMultiSPA"));
            
            // 上传商品主图
            uploadZhutu(imageData,"createSpaModal");
        }
        
    });
    
    // 上传商品主图（编辑时）
    $("#updateZhutu").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("editMultiSPA"));
            
            // 上传商品主图
            uploadZhutu(imageData,"editSpaModal");
        }
        
    });
    
    // 清除file的选择
    $('#selectZhutu').val('');
    $('#updateZhutu').val('');
    
    // 上传商品主图
    function uploadZhutu(imageData,modalId){
        
        // 异步上传
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
                    // 图片预览
                    $('#'+modalId+' .goodsPic_preview').html(
                        '<a href="'+res.url + '" target="blank" title="点击图片查看大图"><img src="'+res.url+'" /></a>'
                    );

                    // 修改按钮的文字
                    $('#'+modalId+' .goodsPic_upload .reUpload .text').text("重新上传");

                    // 将URL添加至表单
                    $("input[name='multiSPA_img']").val(res.url);
                    
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
    
    // 关闭创建单页界面
    hideModal('createSpaModal');
    
    // 关闭编辑单页界面
    hideModal('editSpaModal');
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 如果fromPannel是editSpaModal
    // 需要修改素材库底部的取消按钮的点击事件函数为editSpaModal
    if(fromPannel == 'editSpaModal'){
        
        $('#suCaiKu .modal-footer .btnnav').html(
            '<button type="button" class="default-btn" data-dismiss="modal" onclick="hideSuCaiPannel(\'editSpaModal\')">取消</button>'
        );
        
        // 还需要修改素材库uploadSuCai_fromPannel的value
        // 不修改的话，会造成在选择素材的页面点击上传素材
        // 上传后的素材，点击选择素材后会打开创建单页而不是但会继续编辑
        $('#uploadSuCai_fromPannel').val('editSpaModal');
    }
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'createSpaModal'){
        
        // 上一个面板是 createSpaModal 
        // 渲染出来的关闭按钮是需要返回 createSpaModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'createSpaModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'editSpaModal'){
        
        // 上一个面板是 editSpaModal
        // 渲染出来的关闭按钮是需要返回 editSpaModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'editSpaModal\')">&times;</button>'
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
                    if(fromPannel == 'createSpaModal'){
                        
                        // 更新
                        var clickFunction = 'selectSucaiForZhuitu('+sucai_id+')';
                        
                    }else if(fromPannel == 'editSpaModal'){
                        
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

// 选择当前素材
// 用于创建单页时的主图
function selectSucaiForZhuitu(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiForZhuitu.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将主图Url设置到表单中
                $("input[name='multiSPA_img']").val(res.zhutuImgUrl);
                
                // 设置新的预览
                $('#createSpaModal .goodsPic_preview').html(
                    '<a href="'+res.zhutuImgUrl+'" target="blank" title="点击图片查看大图"><img src="'+res.zhutuImgUrl+'" /></a>'
                );
                
                // 修改从素材库选择素材的按钮文字
                $('#createSpaModal .goodsPic_upload .selectFromSCK').text('重新选择素材');
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                showSuccessResultTimes('已选择',1100);
                
                // 打开创建单页Modal
                setTimeout("showModal('createSpaModal')",1200);
                
                // 解决一个bug
                setTimeout("$('body').attr('class', 'modal-open')",1600);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiForZhuitu.php');
        }
    });
}

// 选择当前素材
// 用于编辑单页时的主图
function selectSucaiEditZhuitu(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiForZhuitu.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将主图Url设置到表单中
                $("input[name='multiSPA_img']").val(res.zhutuImgUrl);
                
                // 设置新的预览
                $('#editSpaModal .goodsPic_preview').html(
                    '<a href="'+res.zhutuImgUrl+'" target="blank" title="点击图片查看大图"><img src="'+res.zhutuImgUrl+'" /></a>'
                );
                
                // 修改从素材库选择素材的按钮文字
                $('#editSpaModal .goodsPic_upload .selectFromSCK').text('重新选择素材');
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                showSuccessResultTimes('已选择',1100);
                
                // 打开编辑单页Modal
                setTimeout("showModal('editSpaModal')",1200);
                
                // 解决一个bug
                setTimeout("$('body').attr('class', 'modal-open')",1600);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiEditZhuitu.php');
        }
    });
}

// 添加文案模板
function p_Template(modalId){
    
    // 将模板换行后添加到最后面
    var textarea = $("#"+modalId+" textarea");
    var p_Template = "<p>文案</p>\n";
    textarea.val(textarea.val() + p_Template);
}

// 添加复制按钮模板
function copy_Template(modalId){
    
    // 将模板换行后添加到最后面
    var textarea = $("#"+modalId+" textarea");
    var copy_Template = "<c data=\"被复制的文案\">一键复制</c>\n";
    textarea.val(textarea.val() + copy_Template);
}

// 为了便于继续操作创建单页
// 素材库的界面关闭后
// 点击右上角X会继续打开创建单页
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏 suCaiKu 面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个 Modal
    if(fromPannel == 'createSpaModal'){
        
        // 显示 createSpaModal
        showModal('createSpaModal')
    }else if(fromPannel == 'editSpaModal'){
        
        // 显示 editSpaModal
        showModal('editSpaModal')
    }
}

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times);
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

// 初始化（getMultiSPAList获取单页列表）
function initialize_getMultiSPAList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        
        // 将所有值清空
        $("input[name='multiSPA_title']").val('');
        $("textarea").val('');
        $("input[name='multiSPA_project']").val('');
        $("input[name='multiSPA_img']").val('');
        $('#createSpaModal .goodsPic_preview').html('<div class="upload_tips" title="无需主图可不上传">主图预览</div>');
        $('#createSpaModal .goodsPic_upload .reUpload .text').text("上传主图");
        $('#createSpaModal .goodsPic_upload .selectFromSCK').text('从素材库选择');
        
        $("select[name='multiSPA_rkym']").empty();
        $("select[name='multiSPA_ldym']").empty();
        $("select[name='multiSPA_dlym']").empty();
        hideResult();
        
    }else if(module == 'edit'){
        
        // 将所有值清空
        $("select[name='multiSPA_rkym']").empty();
        $("select[name='multiSPA_ldym']").empty();
        $("select[name='multiSPA_dlym']").empty();
        hideResult();
    }
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 打开操作反馈
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

// 设置URL路由
function setRouter(pageNum){
    
    // 第一页不设置
    if(pageNum !== 1){
        
        // 根据页码+token设置路由
        window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
    }
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