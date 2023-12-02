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
    	$zm_id = trim($_GET['zm_id']);
    	
        // 过滤参数
        if(empty($zm_id) || !isset($zm_id)){
            
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 验证当前要编辑的zm_id的kf_id发布者是否为当前登录的用户
            // 先获取kf_id
            $getThiszmidKfid = $db->set_table('huoma_kf_zima')->find(['zm_id'=>$zm_id]);
            $kf_id = json_decode(json_encode($getThiszmidKfid))->kf_id;
            
            // 然后再获取kf_creat_user
            $getKfCreateUser = $db->set_table('huoma_kf')->find(['kf_id'=>$kf_id]);
            $kf_creat_user = json_decode(json_encode($getKfCreateUser))->kf_creat_user;
            if($kf_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 获取当前状态
                $zm_status = json_decode(json_encode($getThiszmidKfid))->zm_status;
                
                if($zm_status == 1){
                    
                    // 更新的数据
                    $updateKfzmData = [
                        'zm_status' => 2
                    ];
                    
                    $statusText = '已停用';
                    $new_zm_status = 2;
                }else{
                    
                    // 更新的数据
                    $updateKfzmData = [
                        'zm_status' => 1
                    ];
                    
                    $statusText = '已启用';
                    $new_zm_status = 1;
                }
                
                // 更新的条件
                $updateKfzmCondition = [
                    'zm_id' => $zm_id
                ];
                
                // 提交更新
                $updateKfzm = $db->set_table('huoma_kf_zima')->update($updateKfzmCondition,$updateKfzmData);
                
                // 验证更新结果
                if($updateKfzm){
                    
                    // 更新成功
                    $result = array(
			            'code' => 200,
			            'zm_status' => $new_zm_status,
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