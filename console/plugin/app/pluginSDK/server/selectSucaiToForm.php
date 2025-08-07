<?php

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 获取并清理素材 ID 参数，确保为整数
    $sucai_id = trim(intval($_GET['sucai_id']));
    
    // 参数验证，确保 sucai_id 不为空
    if($sucai_id){
        
        // 启动会话，用于检查用户登录状态
        session_start();
        
        // 判断用户是否已登录
        if(isset($_SESSION["yinliubao"])){
            
            try {
                
                // 引入数据库配置文件
                include '../../../../Db.php';
                
                // 引入公共配置文件（包含协议和域名等信息）
                include '../../../../public/publicConfig.php';
                
                // 创建 PDO 实例，建立数据库连接
                $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
                
                // 设置 PDO 错误模式为异常模式
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 根据素材 ID 查询素材文件名
                $sql = "SELECT sucai_filename FROM huoma_sucai WHERE sucai_id = :sucai_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':sucai_id' => $sucai_id]);
                $getSuCaiFileName = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // 检查查询结果
                if($getSuCaiFileName && $getSuCaiFileName['sucai_filename']){
                    
                    // 获取素材文件名
                    $suCaiFileName = $getSuCaiFileName['sucai_filename'];
                    
                    // 构建素材 URL
                    $suCaiUrl = $protoCol . '://' . $domaiName . dirname(dirname(dirname(dirname(dirname($_SERVER['PHP_SELF']))))) . '/upload/' . $suCaiFileName;
                    
                    // 返回成功结果
                    $result = array(
                        'code' => 200,
                        'msg' => '添加成功',
                        'suCaiUrl' => $suCaiUrl
                    );
                }else{
                    
                    // 无查询结果或文件名为空
                    $result = array(
                        'code' => 202,
                        'msg' => '添加失败'
                    );
                }
                
            } catch(PDOException $e) {
                
                // 捕获数据库操作异常
                $result = array(
                    'code' => 202,
                    'msg' => '添加失败: ' . $e->getMessage()
                );
            }
        }else{
            
            // 未登录
            $result = array(
                'code' => 201,
                'msg' => '未登录'
            );
        }
    }else{
        
        // 非法请求（sucai_id 为空）
        $result = array(
            'code' => 204,
            'msg' => '非法请求'
        );
    }
    
    // 输出 JSON 格式的响应结果，支持中文不转码
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>