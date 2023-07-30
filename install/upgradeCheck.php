<?php
    
    /**
     * 状态码说明
     * 200 需升级
     * 201 无需升级
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 数据库配置
    include '../console/Db.php';
	
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
    
    // 1 检测huoma_qun是否存在qun_today_pv字段、qun_notify字段
    // 2 检测huoma_kf是否存在kf_today_pv字段、kf_onlinetimes字段
    // 3 检测huoma_channel是否存在channel_today_pv字段
    // 4 检测huoma_dwz是否存在dwz_today_pv字段、dwz_android_url、dwz_ios_url、dwz_windows_url字段
    $tablesToCheck = array(
        'huoma_qun' => array('qun_today_pv', 'qun_notify'),
        'huoma_kf' => array('kf_today_pv', 'kf_onlinetimes'),
        'huoma_channel' => array('channel_today_pv'),
        'huoma_dwz' => array('dwz_today_pv', 'dwz_android_url', 'dwz_ios_url', 'dwz_windows_url'),
    );
    
    // 定义一个变量用于标记
    $columnMissing = false;
    
    // 先依次检测表
    foreach ($tablesToCheck as $tableName => $columnsToCheck) {
        
        // 检测这些表是否存在
        $sqlCheckTable = "SHOW TABLES LIKE '$tableName'";
        $result = $conn->query($sqlCheckTable);
        
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

    if (!$columnMissing) {
        
        // 无需升级
        $result = array(
            'code' => 201,
            'msg' => '当前已是最新版！'
        );
    }else{
        
        // 需升级
        $result = array(
            'code' => 200,
            'msg' => '你可以升级至2.0.0版！'
        );
    }
    
    // 关闭数据库连接
    $conn->close();
    
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>