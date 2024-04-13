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
    	$kf_id = trim($_GET['kf_id']);
    	
        // 过滤参数
        if(empty($kf_id) || $kf_id == '' || $kf_id == null || !isset($kf_id)){
            
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
        
        	// 数据库huoma_kf表
        	$huoma_kf = $db->set_table('huoma_kf');
        	
            // 验证当前要删除的kf_id的发布者是否为当前登录的用户
            $getKfid = ['kf_id'=>$kf_id];
            $getKfidResult = $huoma_kf->find($getKfid);
            $kf_creat_user = json_decode(json_encode($getKfidResult))->kf_creat_user;
            
            // 判断操作结果
            if($kf_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                $delKf = ['kf_id'=>$kf_id];
                $delkfResult = $huoma_kf->delete($delKf);
                
                // 判断操作结果
                if($delkfResult){
                    
                    // 将当前kf_id的客服子码也要全部删除
                    // 数据库huoma_kf_zima表
                    $huoma_kf_zima = $db->set_table('huoma_kf_zima');
                    
                    // 操作条件
                    $delKfzm_BythisKfid = ['kf_id'=>$kf_id];
                    
                    // 执行操作
                    $delKfzm_BythisKfidResult = $huoma_kf_zima->delete($delKfzm_BythisKfid);
                    
                    // 操作结果
                    if($delKfzm_BythisKfidResult){
                        
                        // 删除成功
                        $result = array(
        			        'code' => 200,
                            'msg' => '删除成功'
        		        );
                    }else{
                        
                        // 解析报错信息
                        $errorInfo = json_decode(json_encode($delKfzm_BythisKfidResult,true))[2];
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
                    $errorInfo = json_decode(json_encode($delkfResult,true))[2];
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
            'msg' => '未登录或登录失效'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>