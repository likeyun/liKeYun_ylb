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
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 数据库huoma_qun表
        	$huoma_qun = $db->set_table('huoma_qun');
        	
            // 验证当前要删除的qun_id的发布者是否为当前登录的用户
            $where_quninfo = ['qun_id'=>$qun_id];
            $find_quninfo = $huoma_qun->find($where_quninfo);
            $qun_creat_user = json_decode(json_encode($find_quninfo))->qun_creat_user;
            if($qun_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                $where_delqun = ['qun_id'=>$qun_id];
                $delqun = $huoma_qun->delete($where_delqun);
                
                // 判断操作结果
                if($delqun){
                    
                    // 将当前qun_id的子码也要全部删除
                    // 数据库huoma_qun_zima表
                    $huoma_qun_zima = $db->set_table('huoma_qun_zima');
                    $where_del_qun_id_zima = ['qun_id'=>$qun_id];
                    $delzima = $huoma_qun_zima->delete($where_del_qun_id_zima);
                    
                    // 判断操作结果
                    if($delzima){
                        
                        // 删除成功
                        $result = array(
        			        'code' => 200,
                            'msg' => '删除成功'
        		        );
                    }else{
                        
                        // 解析报错信息
                        $errorInfo = json_decode(json_encode($delzima,true))[2];
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
                    
                    // 解析报错信息
                    $errorInfo = json_decode(json_encode($delqun,true))[2];
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
                    'msg' => '删除失败：无法获取到数据，原因：数据已被删除、数据不存在、获取数据失败等...'
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