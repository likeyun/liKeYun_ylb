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
        // 接收参数
    	$qun_id = trim($_GET['qun_id']);
    	
        // 过滤参数
        if(empty($qun_id) || !isset($qun_id)){
            
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
        	
            // 验证当前要编辑的qun_id的发布者是否为当前登录的用户
            $getQunCreateUser = $db->set_table('huoma_qun')->find(['qun_id'=>$qun_id]);
            $qun_creat_user = json_decode(json_encode($getQunCreateUser))->qun_creat_user;
            if($qun_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 获取当前状态
                $qun_status = json_decode(json_encode($getQunCreateUser))->qun_status;
                
                if($qun_status == 1){
                    
                    // 更新的数据
                    $updateQunData = [
                        'qun_status' => 2
                    ];
                    
                    $statusText = '已停用';
                }else{
                    
                    // 更新的数据
                    $updateQunData = [
                        'qun_status' => 1
                    ];
                    
                    $statusText = '已启用';
                }
                
                // 更新的条件
                $updateQunCondition = [
                    'qun_id' => $qun_id,
                    'qun_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $update_qun = $db->set_table('huoma_qun')->update($updateQunCondition,$updateQunData);
                
                // 验证更新结果
                if($update_qun){
                    
                    // 更新成功
                    $result = array(
			            'code' => 200,
                        'msg' => $statusText
		            );
                }else{
                    
                    // 更新失败
                    $result = array(
			            'code' => 202,
                        'msg' => '更新失败'
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
            'msg' => '未登录或登录过期'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>