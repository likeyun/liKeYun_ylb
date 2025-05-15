<?php
    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 201：未登录或卸载失败
     * 202：无管理权限
     * 源码用途：卸载程序，删除数据库表并修改app.json的install=1表示卸载成功
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
            
            // 如果不是管理员（user_admin == 2），不允许卸载
            if($check_admin_result['user_admin'] == 2) {
                $result = array(
                    'code' => 202,
                    'msg' => '卸载失败：没有管理权限！'
                );
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 获取安装状态
            $status = $data['install'];
            
            // 2 表示已安装
            if($status == 2) {
                // 已安装，开始卸载流程
                
                // 定义要删除的表
                $tables = [
                    "DROP TABLE IF EXISTS `ylbPlugin_sdk`",
                    "DROP TABLE IF EXISTS `ylbPlugin_sdk_list`"
                ];
                
                // 执行删除所有表
                foreach ($tables as $tableSql) {
                    $stmt = $pdo->prepare($tableSql);
                    $stmt->execute();
                }
                
                // 设置为未安装
                $data['install'] = 1;
                $data['install_time'] = "";
                $data['current_status'] = "未安装";
                
                // 编码为 JSON 格式，格式化输出且支持中文和斜杠
                $appJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                
                // 写回 JSON 文件
                file_put_contents($jsonFile, $appJsonData);
                
                // 卸载成功
                $result = array(
                    'code' => 200,
                    'msg' => '已卸载'
                );
            }else {
                // 未安装
                $result = array(
                    'code' => 201,
                    'msg' => '卸载失败'
                );
            }
            
        } catch(PDOException $e) {
            // 捕获数据库操作异常
            $result = array(
                'code' => 201,
                'msg' => '卸载失败：' . $e->getMessage()
            );
        }
    }else {
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
    
    // 输出 JSON 格式的响应结果，支持中文不转码
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>