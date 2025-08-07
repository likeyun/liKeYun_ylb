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
    
    $.ajax({
        type: "POST",
        url: "../public/getNavList.php?subPath="+currentPath,
        success: function (res) {

            if (res.code == 200) {
                var $navigation = $('#navList');
                if ($navigation.length === 0) {
                    $navigation = $('.left .dhview ul');
                }

                $navigation.empty(); // 清空原有导航项

                res.navList.forEach(function (item) {
                    var $a = $('<a></a>').attr('href', item.href);
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
                    location.href = "../login/";
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
