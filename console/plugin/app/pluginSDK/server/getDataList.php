<?php

    // 设置响应头为 JSON 格式
    header("Content-type:application/json");
    
    // 启动会话，用于检查用户登录状态
    session_start();
    
    // 判断用户是否已登录
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录，获取当前登录用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 获取页码参数，默认为第1页
        $page = isset($_GET['p']) ? intval($_GET['p']) : 1;
        
        try {
            
            // 引入数据库配置文件
            include '../../../../Db.php';
            
            // 创建 PDO 实例，建立数据库连接
            $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
            
            // 设置 PDO 错误模式为异常模式
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 获取当前用户创建的数据总数
            $sql = "SELECT COUNT(*) FROM ylbPlugin_sdk WHERE data_create_user = :data_create_user";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':data_create_user' => $LoginUser]);
            $totalNum = $stmt->fetchColumn();
            
            // 每页显示数量
            $length = 10;
            
            // 计算每页的起始行
            $offset = ($page - 1) * $length;
            
            // 计算总页数
            $allpage = ceil($totalNum / $length);
            
            // 计算上一页
            $prepage = $page - 1;
            if($page == 1){
                $prepage = 1;
            }
            
            // 计算下一页
            $nextpage = $page + 1;
            if($page == $allpage){
                $nextpage = $allpage;
            }
            
            // 获取当前用户创建的数据列表，按 ID 降序排列
            $sql = "SELECT * FROM ylbPlugin_sdk WHERE data_create_user = :data_create_user ORDER BY ID DESC LIMIT :offset, :length";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':data_create_user', $LoginUser, PDO::PARAM_STR);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);
            $stmt->execute();
            $getDataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 检查查询结果
            if($getDataList){
                
                // 获取成功，返回数据列表和分页信息
                $result = array(
                    'getDataList' => $getDataList,
                    'totalNum' => $totalNum,
                    'prepage' => $prepage,
                    'nextpage' => $nextpage,
                    'allpage' => $allpage,
                    'page' => $page,
                    'code' => 200,
                    'msg' => '获取成功'
                );
            }else{
                
                // 无数据
                $result = array(
                    'code' => 204,
                    'msg' => '暂无数据'
                );
            }
            
        } catch(PDOException $e) {
            
            // 捕获数据库操作异常
            $result = array(
                'code' => 202,
                'msg' => '获取失败: ' . $e->getMessage()
            );
        }
    }else{
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
    
    // 输出 JSON 格式的响应结果，支持中文不转码
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>