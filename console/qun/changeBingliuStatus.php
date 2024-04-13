<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	$bingliu_id = trim($_GET['bingliu_id']);
    	
        // 过滤参数
        if(empty($bingliu_id) || !isset($bingliu_id)){
            
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 验证用户
            $checkUser = $db->set_table('ylb_qun_bingliu')->find(['bingliu_id' => $bingliu_id]);
            $createUser = json_decode(json_encode($checkUser))->createUser;
            if($createUser == $LoginUser){
                
                // 用户一致：允许操作
                // 获取当前状态
                $bingliu_status = json_decode(json_encode($checkUser))->bingliu_status;
                
                if($bingliu_status == 1){
                    
                    // 更新的数据
                    $updateData = ['bingliu_status' => 2];
                    
                    $statusText = '已停用';
                }else{
                    
                    // 更新的数据
                    $updateData = ['bingliu_status' => 1];
                    
                    $statusText = '已启用';
                }
                
                // 提交更新
                $changeBingliuStatus = $db->set_table('ylb_qun_bingliu')->update(['bingliu_id' => $bingliu_id,'createUser' => $LoginUser],$updateData);
                
                // 结果
                if($changeBingliuStatus){
                    
                    // 操作成功
                    $result = array(
			            'code' => 200,
                        'msg' => $statusText
		            );
                }else{
                    
                    // 操作失败
                    $result = array(
			            'code' => 202,
                        'msg' => '操作失败'
		            );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '非法请求'
        		);
            }
        }
    	
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>