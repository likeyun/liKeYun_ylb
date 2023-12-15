<?php
    
    /**
     * 状态码说明
     * 200 需升级
     * 201 无需升级
     */

	// 页面编码
	header("Content-type:application/json");
	
    // 即将更新的版本号
    $newVersion = '2.3.0';
	
	// 数据库配置
    include '../../console/Db.php';
	
    // 连接数据库
	$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
	
    // 验证数据库的连接
	if ($conn->connect_error) {
	    
	   $ret = array(
	       'code' => 202,
	       'msg' => '数据库连接失败'.$conn->connect_error
	   );
	   echo json_encode($ret,JSON_UNESCAPED_UNICODE);
	   die();
    }
    
    // 检查当前版本是不是2.2.0
    if($config['version'] == '2.2.0') {
        
        // 是2.2.0
        $ret = array(
            'code' => 200,
            'msg' => '当前版本' . $config['version'] . '，你可升级至' . $newVersion . '版！'
        ); 
    }else if($config['version'] == '2.3.0') {
        
        // 是2.3.0
        $ret = array(
            'code' => 201,
            'msg' => '当前已是' . $newVersion . '版，无需升级！'
        ); 
    }else {
        
        // 不允许升级
        $ret = array(
            'code' => 201,
            'msg' => '当前版本无法升级，请选择全新安装！'
        );
    }
    
    // 关闭数据库连接
    $conn->close();
    
    // 输出JSON
	echo json_encode($ret, JSON_UNESCAPED_UNICODE);
	
?>