<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	$pic_id = trim($_GET['pic_id']);
    	
        // 过滤参数
        if(empty($pic_id) || !isset($pic_id)){
            
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
        	
            // 验证当前要删除的 pic_id 的添加人是否为当前登录的用户
            $checkUser = $db->set_table('ylb_CarouselSPA_pics')->find(['pic_id' => $pic_id]);
            $add_user = $checkUser['add_user'];
            
            // 判断操作结果
            if($add_user == $LoginUser){
                
                // 用户一致：允许操作
                $delSQL = $db->set_table('ylb_CarouselSPA_pics')->delete(['pic_id' => $pic_id]);
                
                if($delSQL){
                    
                    // 删除成功
                    $result = array(
    			        'code' => 200,
                        'msg' => '已删除'
    		        );
                    
                }else{
                    
                    // 删除失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '删除失败'
        		    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '删除失败：禁止操作'
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