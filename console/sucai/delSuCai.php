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
        
        // 接收参数
    	$sucai_id = trim(intval($_GET['sucai_id']));
    	
        // 过滤参数
        if(empty($sucai_id) || !isset($sucai_id)){
            
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
        	
            // 获取素材信息
            $getSuCaiInfo = $db->set_table('huoma_sucai')->find(['sucai_id'=>$sucai_id]);
            $sucai_upload_user = json_decode(json_encode($getSuCaiInfo))->sucai_upload_user;
            $sucai_filename = json_decode(json_encode($getSuCaiInfo))->sucai_filename;
            
            // 验证当前要删除的sucai_id的上传者是否为当前登录的用户
            if($sucai_upload_user == $LoginUser){
                
                // 用户一致：允许操作
                $delSuCai = $db->set_table('huoma_sucai')->delete(['sucai_id'=>$sucai_id]);
                
                // 判断操作结果
                if($delSuCai){
                    
    		        // 删除本地文件
                    unlink('../upload/'.$sucai_filename);
                    
                    // 删除成功
                    $result = array(
    			        'code' => 200,
                        'msg' => '删除成功'
    		        );
    		        
                }else{
                    
                    // 解析报错信息
                    $errorInfo = json_decode(json_encode($delSuCai,true))[2];
                    if(!$errorInfo){
                        
                        // 如果没有报错信息
                        $errorInfo = '未知';
                    }
                    // 删除失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '删除失败，原因：'.$errorInfo
        		    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '删除失败，错误位置：delSuCai.php'
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