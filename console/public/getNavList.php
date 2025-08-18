<?php

    // 页面编码
    header('Content-Type: application/json');
    
    // 判断登录状态
    session_start();
    if (!isset($_SESSION["yinliubao"])) {
        echo json_encode(['code' => 201, 'msg' => '未登录'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 数据库配置
    include '../Db.php';
    $db = new DB_API($config);
    
    // 当前登录的用户
    $currentUser = $_SESSION["yinliubao"];
    
    // 获取前端传来的路径
    $subPath = trim($_GET['subPath']);
    
    // 定义路径别名映射（可扩展）
    $aliasMap = [
        'kf' => 'qun',
        'channel' => 'qun',
        // 更多别名 => 主路径 可继续添加
    ];
    
    // 提取一级路径名（如 qun/）
    preg_match('#^/?([^/]+)/#', $subPath, $matches);
    $pathKey = $matches[1] ?? '';
    
    if (!$pathKey) {
        echo json_encode(['code' => 400, 'msg' => '路径参数无效'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 将别名映射为主路径
    $normalizedPath = $aliasMap[$pathKey] ?? $pathKey;
    $currentPath = $normalizedPath . '/'; // 格式化为 qun/
    
    // 查询用户信息
    $getCurrentUser = $db->set_table('huoma_user')->find(['user_name' => $currentUser]);
    if (!$getCurrentUser) {
        echo json_encode(['code' => 201, 'msg' => '获取用户信息失败'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 用户组
    $usergroup = $getCurrentUser['user_group'];
    if (!$usergroup) {
        echo json_encode(['code' => 202, 'msg' => '未绑定用户组'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 获取当前用户组的授权菜单
    $getNavList = $db->set_table('ylb_usergroup')->find(['usergroup_name' => $usergroup]);
    if (!$getNavList) {
        echo json_encode(['code' => 202, 'msg' => '无法获取到当前用户组'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 授权菜单
    $navList = $getNavList['navList'];
    $menuItems = json_decode($navList, true);
    
    // 是否授权标记
    $authorized = false;
    
    // 遍历菜单权限，判断是否包含当前路径
    foreach ($menuItems as $item) {
        $hrefPath = str_replace('../', '', $item['href']); // 去除前缀 ../ 得到如 qun/
        if (strpos($currentPath, $hrefPath) !== false || strpos($hrefPath, $currentPath) !== false) {
            $authorized = true;
            break;
        }
    }
    
    // 返回结果
    if (!$authorized) {
        echo json_encode(
            [
                'code' => 404, 
                'msg' => '你所在的用户组无访问该页面的权限',
                'navList' => $menuItems
            ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'code' => 200,
        'msg' => '获取成功',
        'navList' => $menuItems
    ], JSON_UNESCAPED_UNICODE);
    exit;

?>
