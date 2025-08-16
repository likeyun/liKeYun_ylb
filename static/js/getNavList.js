$(document).ready(function () {

    // 当前路径处理
    var currentUrl = window.location.pathname;
    var currentPath = currentUrl.replace(/^\/console\//, '/');

    // 特殊路径映射（将某些路径归并到一个主导航）
    var pathAliasMap = {
        '/kf/': '/qun/',
        '/channel/': '/qun/'
        // 可继续添加更多特殊映射
    };

    // 如果 currentPath 命中映射，则替换为主路径
    if (pathAliasMap[currentPath]) {
        currentPath = pathAliasMap[currentPath];
    }
    
    // 如果路径中包含 plugin/app，请求地址需要加深
    let reqURL;
    if (currentPath.indexOf('plugin/app') !== -1) {
        
        // 包含
        reqURL = "../../../public/getNavList.php?subPath="+currentPath;
    }else {
        
        // 未包含
        reqURL = "../public/getNavList.php?subPath="+currentPath;
    }
    
    // 请求
    $.ajax({
        type: "POST",
        url: reqURL,
        success: function (res) {

            if (res.code == 200) {
                var $navigation = $('#navList');
                if ($navigation.length === 0) {
                    $navigation = $('.left .dhview ul');
                }

                $navigation.empty(); // 清空原有导航项

                res.navList.forEach(function (item) {
                    
                    // 如果当前页面路径包含 plugin/app/，给 item.href 前加 ../../
                    var itemHref = item.href;
                    if (currentPath.indexOf('plugin/app/') !== -1) {
                        
                        // 先去掉原本开头的 ./ 或 ../ 再加 ../../，防止路径重复
                        itemHref = '../../../' + item.href.replace(/^(\.\/|\.\.\/)*/, ''); 
                    }
                    
                    var $a = $('<a></a>').attr('href', itemHref);
                    var $li = $('<li></li>').addClass('nav-li');

                    // 判断是否选中：先处理 item.href
                    var hrefPath = item.href.replace('../', '/');
                    var isSelected = currentPath.indexOf(hrefPath) === 0;

                    // 拼接 icon 类
                    var iconClass = isSelected
                        ? item.icon.replace(/(-dark)?$/, '-dark')
                        : item.icon;

                    var $icon = $('<i></i>').addClass('icon').addClass(iconClass);
                    var $span = $('<span></span>').addClass('nav-text').text(item.text);

                    if (isSelected) {
                        $a.addClass('selected');
                    }

                    $li.append($icon).append($span);
                    $a.append($li);
                    $navigation.append($a);
                });
            } else {
                if (res.code == 201) {
                    
                    // 未登录
                    // 根据所在页面深度不同，有不同的跳转路径
                    if (currentPath.indexOf('plugin/app/') !== -1) {
                        
                        // 登录页面路径加深
                        location.href = '../../../login/?f=getNavList';
                    }else {
                        
                        // 默认
                        location.href = '../login/?f=getNavList';
                    }
                } else {
                    
                    // 无权限
                    $("body").html("<h1 style='text-align:center;margin-top:100px;'>"+res.msg+"</h1>");
                }
            }
        },
        error: function () {
            errorPage('getNavList.php');
        }
    });
});
