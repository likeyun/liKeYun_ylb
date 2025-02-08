<?php

    // 返回JSON
    header("Content-type:application/json");
    
    // 引入数据库配置
    include '../../console/Db.php';
    
    // 设置查询的 carousel_id
    $carousel_id = intval(trim($_GET['carousel_id']));
    
    if ($carousel_id) {
        try {
            // 创建PDO连接
            $pdo = new PDO(
                'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'],
                $config['db_user'],
                $config['db_pass']
            );
            
            // 设置PDO错误模式
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 开始事务
            $pdo->beginTransaction();
    
            // 编写SQL查询语句，查询 ylb_CarouselSPA
            $sql = "SELECT Carousel_title,Carousel_status FROM ylb_CarouselSPA WHERE Carousel_id = :carousel_id";
            
            // 准备语句
            $stmt = $pdo->prepare($sql);
            
            // 绑定参数
            $stmt->bindParam(':carousel_id', $carousel_id, PDO::PARAM_INT);
            
            // 执行查询
            $stmt->execute();
            
            // 获取查询结果
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 如果 ylb_CarouselSPA 查询成功
            if ($results) {
                
                if($results[0]['Carousel_status'] == 2) {
                    
                    // 状态：停用
                    $result = array(
                        'code' => -4,
                        'msg' => '该页面已被管理员停止使用'
                    );
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                // 获取 carousel_id 后，查询 ylb_CarouselSPA_pics
                $sqlPics = "SELECT pic_id,pic_url,pic_desc,show_copy_btn FROM ylb_CarouselSPA_pics WHERE Carousel_id = :carousel_id";
                $stmtPics = $pdo->prepare($sqlPics);
                $stmtPics->bindParam(':carousel_id', $carousel_id, PDO::PARAM_INT);
                $stmtPics->execute();
                
                // 获取图片表的结果
                $picsResults = $stmtPics->fetchAll(PDO::FETCH_ASSOC);
                
                // 更新 Carousel_pv 字段 +1
                $updateSql = "UPDATE ylb_CarouselSPA SET Carousel_pv = Carousel_pv + 1 WHERE Carousel_id = :carousel_id";
                $stmtUpdate = $pdo->prepare($updateSql);
                $stmtUpdate->bindParam(':carousel_id', $carousel_id, PDO::PARAM_INT);
                $stmtUpdate->execute();
                
                // 提交事务
                $pdo->commit();
                
                // 合并数据
                $result = array(
                    'code' => 0,
                    'msg' => '获取成功',
                    'carousel_datas' => array(
                        'carousel_info' => $results,
                        'carousel_pics' => $picsResults
                    )
                );
            } else {
                
                // 无数据
                $result = array(
                    'code' => -1,
                    'msg' => '获取页面信息失败'
                );
            }
            
        } catch (PDOException $e) {
            // 回滚事务
            $pdo->rollBack();
            
            // 数据库连接失败
            $result = array(
                'code' => -2,
                'msg' => '数据库服务器连接失败: ' . $e->getMessage()
            );
        }
    } else {
        // 参数缺失
        $result = array(
            'code' => -3,
            'msg' => '参数有误'
        );
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>
