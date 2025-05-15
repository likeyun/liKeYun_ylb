<?php
    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 201：未登录或安装失败
     * 202：无权限或目录权限不足
     * 源码用途：安装程序，修改app.json的install=2表示安装成功
     * 作者：TANKING
     */

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 启动会话，用于检查用户登录状态
    session_start();
    
    // 判断用户是否已登录
    if(isset($_SESSION["yinliubao"])){
        
        // 获取当前登录用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 读取 JSON 配置文件
        $jsonFile = '../app.json';
        $jsonData = file_get_contents($jsonFile);
        
        // 检测配置文件目录权限（需 755 或更高）
        if(!installPermission('../../')) {
            $result = array(
                'code' => 202,
                'msg' => '安装失败，失败原因：console/plugin/app 目录没有755权限！请前往服务器修改权限！'
            );
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 解码 JSON 数据
        $data = json_decode($jsonData, true);
        
        try {
            // 引入数据库配置文件
            include '../../../../Db.php';
            
            // 创建 PDO 实例，建立数据库连接
            $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
            // 设置 PDO 错误模式为异常模式
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 验证当前登录用户是否为管理员
            $sql = "SELECT user_admin FROM huoma_user WHERE user_name = :user_name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_name' => $LoginUser]);
            $check_admin_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 如果不是管理员（user_admin == 2），不允许安装
            if($check_admin_result['user_admin'] == 2) {
                $result = array(
                    'code' => 202,
                    'msg' => '安装失败：没有安装权限！'
                );
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 获取安装状态
            $status = $data['install'];
            
            // 1 表示未安装
            if($status == 1) {
                // 未安装，开始安装流程
                
                // 设置为已安装
                $data['install'] = 2;
                $data['install_time'] = date('Y-m-d H:i:s');
                $data['current_status'] = "已安装";
                
                // 编码为 JSON 格式，格式化输出且支持中文和斜杠
                $appJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                
                // 写回 JSON 文件
                file_put_contents($jsonFile, $appJsonData);
                
                // 定义要创建的表
                $tables = [
                    
                    // 表 1: ylbPlugin_sdk
                    "CREATE TABLE IF NOT EXISTS `ylbPlugin_sdk` (
                        `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
                        `data_id` INT(10) DEFAULT NULL COMMENT '页面ID',
                        `data_title` VARCHAR(64) DEFAULT NULL COMMENT '标题',
                        `data_pic` TEXT DEFAULT NULL COMMENT '图片',
                        `data_limit` INT(1) DEFAULT '1' COMMENT '访问限制 1不限制 2仅限手机 3仅 Malay限微信 4仅限QQ 5仅限抖音',
                        `data_pv` INT(10) DEFAULT '0' COMMENT '访问次数',
                        `data_status` INT(1) DEFAULT '1' COMMENT '状态 1正常 2停用',
                        `data_dlym` TEXT DEFAULT NULL COMMENT '短链域名',
                        `data_rkym` TEXT DEFAULT NULL COMMENT '入口域名',
                        `data_ldym` TEXT DEFAULT NULL COMMENT '落地域名',
                        `data_jumplink` TEXT DEFAULT NULL COMMENT '目标地址',
                        `data_expire_time` VARCHAR(32) DEFAULT NULL COMMENT '到期时间',
                        `data_create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                        `data_key` VARCHAR(10) DEFAULT NULL COMMENT '短网址Key',
                        `data_create_user` VARCHAR(32) DEFAULT NULL COMMENT '创建者'
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='引流宝插件开发示例'",
                    
                    // 表 2: ylbPlugin_sdk_list
                    "CREATE TABLE IF NOT EXISTS `ylbPlugin_sdk_list` (
                        `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
                        `data_id` INT(10) DEFAULT NULL COMMENT '上一级的id',
                        `listdata_id` INT(10) DEFAULT NULL COMMENT '自定义ID',
                        `listdata_1` VARCHAR(32) DEFAULT NULL COMMENT '字段1',
                        `listdata_2` VARCHAR(64) DEFAULT NULL COMMENT '字段2',
                        `listdata_3` TEXT DEFAULT NULL COMMENT '字段3',
                        `listdata_4` INT(1) DEFAULT '1' COMMENT '字段4',
                        `listdata_status` INT(1) DEFAULT '1' COMMENT '状态 1正常 2停用',
                        `listdata_addtime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
                        `listdata_adduser` VARCHAR(32) DEFAULT NULL COMMENT '添加人'
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='引流宝插件开发示例list表'"
                ];
                
                // 执行创建所有表
                foreach ($tables as $tableSql) {
                    $stmt = $pdo->prepare($tableSql);
                    $stmt->execute();
                }
                
                // 安装成功
                $result = array(
                    'code' => 200,
                    'msg' => '安装成功'
                );
                
            }else {
                // 已安装
                $result = array(
                    'code' => 201,
                    'msg' => '安装失败：当前插件已安装！'
                );
            }
            
        } catch(PDOException $e) {
            // 捕获数据库操作异常
            $result = array(
                'code' => 201,
                'msg' => '安装失败：' . $e->getMessage()
            );
        }
    }else {
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
    
    // 检测配置文件目录权限
    function installPermission($dir) {
        // 检查目录是否存在
        if (!is_dir($dir)) {
            return false;
        }
        
        // 检查目录权限是否满足 755
        $perms = fileperms($dir);
        return ($perms & 0x1FF) >= 0755;
    }
    
    // 输出 JSON 格式的响应结果，支持中文不转码
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>