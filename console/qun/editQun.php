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
    	$qun_title = trim($_POST['qun_title']);
    	$qun_beizhu = trim($_POST['qun_beizhu']);
    	$qun_rkym = trim($_POST['qun_rkym']);
    	$qun_ldym = trim($_POST['qun_ldym']);
    	$qun_dlym = trim($_POST['qun_dlym']);
    	$qun_status = trim($_POST['qun_status']);
    	$qun_kf_status = trim($_POST['qun_kf_status']);
    	$qun_kf = trim($_POST['qun_kf']);
    	$qun_safety = trim($_POST['qun_safety']);
    	$qun_qc = trim($_POST['qun_qc']);
    	$qun_id = trim($_POST['qun_id']);
    	
        // 过滤参数
        if(empty($qun_title) || $qun_title == '' || $qun_title == null || !isset($qun_title)){
            
            $result = array(
			    'code' => 203,
                'msg' => '标题未填写'
		    );
        }else if(empty($qun_rkym) || $qun_rkym == '' || $qun_rkym == null || !isset($qun_rkym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '入口域名未选择'
		    );
        }else if(empty($qun_ldym) || $qun_ldym == '' || $qun_ldym == null || !isset($qun_ldym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '落地域名未选择'
		    );
        }else if(empty($qun_dlym) || $qun_dlym == '' || $qun_dlym == null || !isset($qun_dlym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '短链域名未选择'
		    );
        }else if(empty($qun_status) || $qun_status == '' || $qun_status == null || !isset($qun_status)){
            
            $result = array(
			    'code' => 203,
                'msg' => '群状态未设置'
		    );
        }else if(empty($qun_kf_status) || $qun_kf_status == '' || $qun_kf_status == null || !isset($qun_kf_status)){
            
            $result = array(
			    'code' => 203,
                'msg' => '客服状态未设置'
		    );
        }else if(empty($qun_kf) && $qun_kf_status == '1'){
            
            // 当客服二维码状态为1（显示）的时候才判断是否有上传二维码
            $result = array(
			    'code' => 203,
                'msg' => '客服二维码未上传'
		    );
        }else if(empty($qun_safety) || $qun_safety == '' || $qun_safety == null || !isset($qun_safety)){
            
            $result = array(
			    'code' => 203,
                'msg' => '顶部扫码安全提示未设置'
		    );
        }else if(empty($qun_qc) || $qun_qc == '' || $qun_qc == null || !isset($qun_qc)){
            
            $result = array(
			    'code' => 203,
                'msg' => '去重功能未选择'
		    );
        }else if(empty($qun_id) || $qun_id == '' || $qun_id == null || !isset($qun_id)){
            
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
        	
            // 验证当前要编辑的qun_id的发布者是否为当前登录的用户
            $where_quninfo = ['qun_id'=>$qun_id];
            $find_quninfo = $huoma_qun->find($where_quninfo);
            $qun_creat_user = json_decode(json_encode($find_quninfo))->qun_creat_user;
            if($qun_creat_user == $LoginUser){
                
                $qun_title = trim($_POST['qun_title']);
                $qun_beizhu = trim($_POST['qun_beizhu']);
                $qun_rkym = trim($_POST['qun_rkym']);
                $qun_ldym = trim($_POST['qun_ldym']);
                $qun_dlym = trim($_POST['qun_dlym']);
                $qun_status = trim($_POST['qun_status']);
                $qun_kf_status = trim($_POST['qun_kf_status']);
                $qun_kf = trim($_POST['qun_kf']);
                $qun_safety = trim($_POST['qun_safety']);
                $qun_qc = trim($_POST['qun_qc']);
                $qun_id = trim($_POST['qun_id']);
                
                // 用户一致：允许操作
                // 更新的数据
                $updateQunData = [
                    'qun_title' => $qun_title,
                    'qun_beizhu' => $qun_beizhu,
                    'qun_rkym' => $qun_rkym,
                    'qun_ldym' => $qun_ldym,
                    'qun_dlym' => $qun_dlym,
                    'qun_status' => $qun_status,
                    'qun_kf_status' => $qun_kf_status,
                    'qun_kf' => $qun_kf,
                    'qun_safety' => $qun_safety,
                    'qun_qc' => $qun_qc
                ];
                
                // 更新的条件
                $updateQunCondition = [
                    'qun_id' => $qun_id,
                    'qun_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $update_qun = $huoma_qun->update($updateQunCondition,$updateQunData);
                
                // 验证更新结果
                if($update_qun){
                    
                    // 更新成功
                    $result = array(
			            'code' => 200,
                        'msg' => '更新成功'
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
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>