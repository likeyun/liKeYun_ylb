<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="wechat-enable-text-zoom-em" content="true">
    <meta name="color-scheme" content="light dark">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" href="https://res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico">
    <link rel="stylesheet" href="../../static/css/common.css">
    <link rel="stylesheet" href="../../static/css/bootstrap.min.css">
    <script src="../../static/js/qrcode.min.js"></script>
    <title>加载中...</title>
</head>
<body>

<?php

    // 编码
    header("Content-type:text/html;charset=utf-8");
    
    // 获取参数并过滤
    $key = isset($_GET['key']) ? trim($_GET['key']) : '';
    $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
    
    // 非法字符和SQL关键词过滤
    if (!$key || preg_match('/[<>\'\"\/\\\[\];:()=+\*\?`~!@#$%^&{}]/', $key) || preg_match('/(select|update|drop|insert|create|delete|where|join|script)/i', $key)) {
        echo warnInfo('温馨提示', '该链接不安全，请重新生成！');
        exit;
    }
    
    // 引入数据库
    require_once '../console/Db.php';
    $db = new DB_API($config);
    
    // 引流宝安装的目录级别（根目录、二级目录、三级目录）
    $folderNum = $config['folderNum'];
    
    // 落地页目录名（默认common）
    // 可自己改，改了这个地方得去根目录也要将common改成你设置的目录名
    $folderName = "common";
    
    // 路由映射定义
    $routeMap = [
        ['table' => 'huoma_qun',           'type' => 'qun',      'idField' => 'qun_id',      'rkymField' => 'qun_rkym',      'param' => 'qid'],
        ['table' => 'huoma_kf',            'type' => 'kf',       'idField' => 'kf_id',       'rkymField' => 'kf_rkym',       'param' => 'kid'],
        ['table' => 'huoma_channel',       'type' => 'channel',  'idField' => 'channel_id',  'rkymField' => 'channel_rkym',  'param' => 'cid'],
        ['table' => 'huoma_tbk',           'type' => 'zjy',      'idField' => 'zjy_id',      'rkymField' => 'zjy_rkym',      'param' => 'zid'],
        ['table' => 'huoma_tbk_mutiSPA',   'type' => 'multiSPA', 'idField' => 'multiSPA_id', 'rkymField' => 'multiSPA_rkym', 'param' => 'mid']
    ];
    
    // 主查询逻辑
    foreach ($routeMap as $route) {
        $res = $db->set_table($route['table'])->find([$route['type'] . '_key' => $key]);
        if ($res) {
            $rkym = $res[$route['rkymField']];
            $id = $res[$route['idField']];
            jumpTo($folderNum, $rkym, $route['type'], $route['param'], $id, $folderName);
            exit;
        }
    }
    
    // 并流跳转逻辑
    $bingliu = $db->set_table('ylb_qun_bingliu')->find(['before_qun_key' => $key]);
    if ($bingliu && $bingliu['bingliu_status'] == 1) {
        $laterQun = $db->set_table('huoma_qun')->find(['qun_id' => $bingliu['later_qun_id']]);
        if ($laterQun) {
            $db->set_table('ylb_qun_bingliu')->update(['before_qun_key' => $key], ['bingliu_num' => $bingliu['bingliu_num'] + 1]);
            jumpTo($folderNum, $laterQun['qun_rkym'], 'qun', 'qid', $bingliu['later_qun_id'], $folderName);
            exit;
        }
    }
    
    echo warnInfo('温馨提示', '链接不存在或已被管理员删除');
    exit;
    
    // ======================= 通用函数 ========================
    
    // 跳转到入口域名的落地页
    function jumpTo($folderNum, $rkym, $type, $paramName, $id, $folderName) {
        $redirectPath = ($folderNum == 1)
            ? "/$folderName/$type/redirect/?$paramName=$id&t=" . time()
            : '/' . redirectURL($folderNum) . "/$folderName/$type/redirect/?$paramName=$id&t=" . time();
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $rkym$redirectPath");
        exit;
    }
    
    // 重定向
    function redirectURL($folderNum) {
        $dirs = [];
        $dir = __DIR__;
        for ($i = 0; $i < $folderNum - 1; $i++) {
            $dirs[] = basename($dir);
            $dir = dirname($dir);
        }
        return implode('/', array_reverse($dirs));
    }
    
    // 提示
    function warnInfo($title, $message) {
        return <<<HTML
    <title>$title</title>
    <div id="warnning">
        <img src="../../../static/img/warn.png" />
    </div>
    <p id="warnText">$message</p>
HTML;
    }
?>

</body>
</html>