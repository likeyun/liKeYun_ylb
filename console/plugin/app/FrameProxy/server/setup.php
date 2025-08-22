<?php

    /**
     * 插件安装接口
     * 状态码说明：
     * 200 → 安装成功
     * 其它状态码自定义
     * 作者：TANKING
     */
    
    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
    
        $LoginUser = $_SESSION["yinliubao"];
    
        // 读取JSON文件内容
        $jsonFile = '../app.json';
        $jsonData = file_get_contents($jsonFile);
    
        // 检测目录权限
        if(!installPermission('../../')){
            $result = [
                'code' => 202,
                'msg' => '安装失败，失败原因：console/plugin/app 目录没有755权限！请前往服务器修改权限！'
            ];
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        $data = json_decode($jsonData, true);
    
        // 数据库配置
        include '../../../../Db.php';
    
        try {
            // PDO连接
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // 检查是否管理员
            $stmt = $pdo->prepare("SELECT user_admin FROM huoma_user WHERE user_name = :username LIMIT 1");
            $stmt->execute([':username' => $LoginUser]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if(!$user || $user['user_admin'] == 2){
                $result = ['code' => 202, 'msg' => '安装失败：没有安装权限！'];
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                exit;
            }
    
            // 检查安装状态
            $status = $data['install'] ?? 1;
            if($status == 1){
                // 设置为已安装
                $data['install'] = 2;
                $data['install_time'] = date('Y-m-d H:i:s');
                $data['current_status'] = "已安装";
    
                // 写回JSON文件
                $appJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                file_put_contents($jsonFile, $appJsonData);
    
                // 创建表
                $sql = "CREATE TABLE IF NOT EXISTS `ylbPlugin_wxdmQk` (
                  `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
                  `data_id` int(10) DEFAULT NULL COMMENT '页面ID',
                  `data_title` varchar(64) DEFAULT NULL COMMENT '标题',
                  `data_pic` text DEFAULT NULL COMMENT '图片',
                  `data_pv` int(10) DEFAULT '0' COMMENT '访问次数',
                  `data_status` int(1) DEFAULT '1' COMMENT '状态 1正常 2停用',
                  `data_mode` int(1) DEFAULT '1' COMMENT '模式 1框架 2引导',
                  `data_dxccym` text DEFAULT NULL COMMENT '落地域名',
                  `data_jumplink` text DEFAULT NULL COMMENT '目标地址',
                  `data_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                  `data_key` varchar(10) DEFAULT NULL COMMENT 'Key',
                  `data_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='链接列表'";
    
                $pdo->exec($sql);
    
                $result = ['code' => 200, 'msg' => '安装成功'];
    
            }else{
                $result = ['code' => 201, 'msg' => '安装失败：当前插件已安装！'];
            }
    
        } catch (PDOException $e){
            $result = ['code' => 500, 'msg' => '数据库错误: '.$e->getMessage()];
        }
    
    }else{
        $result = ['code' => 201, 'msg' => '未登录'];
    }
    
    // 检测目录权限函数
    function installPermission($dir) {
        if (!is_dir($dir)) return false;
        $perms = fileperms($dir);
        return ($perms & 0x1FF) >= 0755;
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>
