<?php

    header("Content-type:application/json");

    function getAllAppJsonData($directory) {
        $result = [];
    
        // 获取目录中的所有文件和子目录
        $items = scandir($directory);
    
        // 遍历每一个文件或子目录
        foreach ($items as $item) {
            
            // 忽略当前目录(.)和上级目录(..)
            if ($item == '.' || $item == '..') {
                continue;
            }
    
            // 构建完整的路径
            $path = $directory . '/' . $item;
    
            // 如果是目录，则递归调用该函数
            if (is_dir($path)) {
                
                // 递归调用函数
                $result = array_merge($result, getAllAppJsonData($path));
            } elseif (is_file($path) && $item == 'app.json') {
                
                // 如果是文件且文件名是app.json，则解析JSON并添加到结果数组
                $jsonContent = file_get_contents($path);
                $jsonData = json_decode($jsonContent, true);
    
                // 检查JSON解析是否成功
                if ($jsonData !== null) {
                    $result[] = $jsonData;
                }
            }
        }
    
        return $result;
    }
    
    // 当前目录下的app目录
    $directory = __DIR__ . '/app';
    
    // 获取所有app.json的数据
    $appJsonData = getAllAppJsonData($directory);
    
    // 验证登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        $loginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
        include '../Db.php';
        
        // 实例化类
        $db = new DB_API($config);
        
        // 验证管理权限
        $checkUser = $db->set_table('huoma_user')->find(['user_name' => $loginUser]);
        
        if(count($appJsonData) > 0) {
        
            // 结果
            $ret = array(
                'code' => 200,
                'msg' => '获取成功',
                'pluginArray' => $appJsonData,
                'user_admin' => $checkUser['user_admin']
            );
        }else {
            
            // 结果
            $ret = array(
                'code' => 202,
                'msg' => '暂无插件'
            );
        }
    }else {
        
        // 未登录
        $ret = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
    
    // 打印结果
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    
?>
