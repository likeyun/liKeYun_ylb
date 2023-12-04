<?php
    
    /**
     * 状态码说明
     * 200 需升级
     * 201 无需升级
     */

	// 页面编码
	header("Content-type:application/json");
	
    // 即将更新的版本号
    $newVersion = '2.2.0';
	
	// 数据库配置
    include '../../console/Db.php';
	
    // 连接数据库
	$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
	
    // 验证数据库的连接
	if ($conn->connect_error) {
	    
	   $result = array(
	       'code' => 202,
	       'msg' => '数据库连接失败'.$conn->connect_error
	   );
	   echo json_encode($result,JSON_UNESCAPED_UNICODE);
	   die();
    }
    
    // 检测ylb_usergroup表是否存在以下字段
    $tablesToCheck = array(
        'ylb_usergroup' => array(
            'id',
            'usergroup_id',
            'usergroup_name'
        )
    );
    
    // 定义一个变量用于标记
    $columnMissing = false;
    
    // 先依次检测表
    foreach ($tablesToCheck as $tableName => $columnsToCheck) {
        
        // 检测这些表是否存在
        $sqlCheckTable = "SHOW TABLES LIKE '$tableName'";
        
        // 表存在
        if ($result->num_rows > 0) {
            
            // 后依次检测字段
            foreach ($columnsToCheck as $columnName) {
                
                // 检测这些字段是否存在
                $sqlCheckColumn = "SHOW COLUMNS FROM $tableName LIKE '$columnName'";
                $resultColumn = $conn->query($sqlCheckColumn);
                
                // 字段不存在
                if ($resultColumn->num_rows == 0) {
                    $columnMissing = true;
                }
            }
        } else {
            
            // 表不存在
            $columnMissing = true;
        }
    }
    
    // 标记为False
    // 代表检测的项全部都存在
    if (!$columnMissing) {
        
        // 无需升级
        if($config['version'] == $newVersion) {
           
           $result = array(
                'code' => 201,
                'msg' => '当前已是' . $newVersion . '版，无需升级！'
            ); 
        }
        
        if($config['version'] > $newVersion) {
           
           $result = array(
                'code' => 201,
                'msg' => '当前是' . $config['version'] . '版，无需升级！'
            ); 
        }
    }else{
        
        // 否则就是不存在或不完整
        // 需升级
        $result = array(
            'code' => 200,
            'msg' => '当前版本' . $config['version'] . '，你可升级至' . $newVersion . '版！'
        );
    }
    
    // 关闭数据库连接
    $conn->close();
    
    // 输出JSON
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	
?>