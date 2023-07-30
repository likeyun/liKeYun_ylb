
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的列表
        getSuCaiList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getSuCaiList();
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
                // 账号信息
                var $accountInfo_HTML = $(
                    '<span class="user_name">'+res.user_name+'</span>' +
                    '<span onclick="exitLogin();">退出</span>'
                );
                $("#accountInfo").html($accountInfo_HTML);
                
                // 初始化
                initialize_Login('login',res.user_admin)
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
                
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

document.addEventListener('DOMContentLoaded', function() {
    
    // 选择素材
    $("#selectSuCai").change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("uploadSuCai"));
            
            // 上传至素材库
            uploadToSuCaiKu(imageData);
        }
        
    });
    
    // 上传至素材库
    function uploadToSuCaiKu(imageData){

        $.ajax({
            url: "../public/uploadToSuCaiKu.php",
            type: "POST",
            data: imageData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(res) {
                
                if(res.code == 200){
                    
                    // 修改上传按钮文字
                    $("#right .data-card .button-view .upimg span").text('上传素材');
                    
                    // 上传成功
                    showNotification(res.msg);
                    
                    // 获取最新列表
                    getSuCaiList(1);
                    
                }else{
                    
                    // 上传失败
                    showNotification(res.msg);
                    
                    // 修改上传按钮文字
                    $("#right .data-card .button-view .upimg span").text('重新上传');
                }
            },
            error: function() {
                
                // 上传失败
                showNotification('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！');
            },
            beforeSend: function(res){
                
                // 修改上传按钮文字
                $("#right .data-card .button-view .upimg span").text('正在上传...');
            }
        });
        
        // 清空file选择的文件以重试
        $("#selectSuCai").val('');
    }
})

// 获取素材列表
function getSuCaiList(pageNum) {
    
    // 初始化
    initialize_getSuCaiList();
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "../public/getSuCaiList.php?num=20&p=1";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "../public/getSuCaiList.php?num=20&p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 分页控件
                if(res.totalNum > 20){
                    
                    // 渲染分页控件
                    suCaifenyeControl(pageNum,res.nextpage,res.prepage,res.allpage);
                    
                }else{
                    
                    // 隐藏分页控件
                    $('#right .data-list .fy').css('display','none');
                }

                // 遍历数据
                for (var i=0; i<res.suCaiList.length; i++) {
                    
                    // 素材ID
                    var sucai_id = res.suCaiList[i].sucai_id;
                    
                    // 素材备注
                    var sucai_beizhu = res.suCaiList[i].sucai_beizhu;
                    
                    // 素材文件名
                    var sucai_filename = res.suCaiList[i].sucai_filename;
                    
                    // 列表
                    var $sucaiList_HTML = $(
                    '<div class="sucai-item" id="'+sucai_id+'" onclick="toggleSelection('+sucai_id+')">' +
                    '   <div class="sucai-thumb">' +
                    '       <img src="../upload/'+sucai_filename+'" title="鼠标右键另存为" />' +
                    '   </div>' +
                    '   <div class="sucai-filename" title="'+sucai_filename+'">'+sucai_filename+'</div>' +
                    '</div>'
                    );
                    $("#right .data-list .grid-sucai").append($sucaiList_HTML);
                }
                
            }else{
                
                // 如果是未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 无数据
                noData(res.msg);
            }
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getSuCaiList.php');
      },
      beforeSend: function(res){
        
        // 正在加载
        $("#right .data-list .sucai-Pannel .jiazai").html('正在加载，请稍等...');
      },
      complete: function(res){
          
          $("#right .data-list .sucai-Pannel .jiazai").html('');
      }
    });
}

// 素材库分页控件
function suCaifenyeControl(thisPage,nextPage,prePage,allPage){

    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1 且 总页码=1
        // 无需显示分页控件
        $('#right .data-list .fy').css('display','none');
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1 且 总页码>1
        // 代表还有下一页
        // 需要显示下一页、最后一页控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#right .data-list .fy').css('display','block');
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        // 需要显示第一页、上一页控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#right .data-list .fy').css('display','block');
        
    }else{
        
        // 其他情况
        // 需要显示所有控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#right .data-list .fy').css('display','block');
    }
    
    // 渲染分页控件
    $('#right .data-list .fy').html($suCaiFenye);
}

// 获取素材库分页数据
function getSuCaiFenyeData(e){
    
    var pageNum_ = e.id;
    
    // 获取该页列表
    getSuCaiList(pageNum_);
}

// 批量删除
var selectedArray = [];

// 批量选择素材事件
function toggleSelection(sucai_id) {
    
    // 获取点击的项目
    var selected_sucai = document.getElementById(sucai_id);
    var sucai_index = selectedArray.indexOf(sucai_id);

    if (sucai_index > -1) {
    
        // 如果本身是已选中
        // 移除选中样式并从列表中移除
        selected_sucai.classList.remove('sucai-item-selected');
        selectedArray.splice(sucai_index, 1);
    } else {
    
        // 如果本身是未选中
        // 添加选中样式并添加到列表中
        selected_sucai.classList.add('sucai-item-selected');
        selectedArray.push(sucai_id);
    }
    
    // 有选中才会显示批量删除的按钮
    if(selectedArray.length > 0){
        
        // 显示
        $('#right .data-card .button-view .pldel').css('display','block');
    }else{
        
        // 隐藏
        $('#right .data-card .button-view .pldel').css('display','none');
    }
}

// 批量删除素材
function delSuCai(){
    
    // 定义一个参数以记录删除次数
    var delNum = 0;
    
    // 循环任务
    for (var i = 0; i < selectedArray.length; i++) {
        
        // 遍历每一项的id
        var sucaiId = selectedArray[i];
        
        // 执行每一个id的删除
        $.ajax({
            type: "POST",
            url: "./delSuCai.php?sucai_id="+sucaiId,
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // 打印每一次的删除结果
                    console.log(res.msg+delNum+'次')
                }else{
                    
                    // 删除失败
                    showNotification(res.msg);
                }
            },
            error: function() {
                
                // 服务器发生错误
                showNotification('服务器发生错误');
            }
        });
        
        // 记录删除次数
        var delNum = i+1;
        
        // 通过操作DOM的方式删除选中的节点
        var selected_sucai = document.getElementById(sucaiId);
        selected_sucai.parentNode.removeChild(selected_sucai);
    }

    // 清空选中列表
    selectedArray = [];
    
    // 有选中才会显示批量删除的按钮
    if(selectedArray.length > 0){
        
        // 显示
        $('#right .data-card .button-view .pldel').css('display','block');
    }else{
        
        // 隐藏
        $('#right .data-card .button-view .pldel').css('display','none');
    }
    
    // 隐藏Modal
    hideModal('delSuCaiModel');
    
    // 获取素材列表
    setTimeout('getSuCaiList(1)',1000);
    
    // 弹出提示
    setTimeout('showNotification("删除成功")',1300);
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getSuCaiList(pageNum);
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

// 顶部操作结果信息提示框
function showTopAlert(content){
    $('#topAlert').text(content);
    $('#topAlert').css('display','block');
    setTimeout('hideTopAlert()', 2000); // 2.5秒后自动关闭
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

// 暂无数据
function noData(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noRes.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化（getSuCaiList）
function initialize_getSuCaiList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list .sucai-Pannel .grid-sucai").empty('');
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