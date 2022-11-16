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
    	$kf_title = trim($_POST['kf_title']);
    	$kf_beizhu = trim($_POST['kf_beizhu']);
    	$kf_rkym = trim($_POST['kf_rkym']);
    	$kf_ldym = trim($_POST['kf_ldym']);
    	$kf_dlym = trim($_POST['kf_dlym']);
    	$kf_model = trim($_POST['kf_model']);
    	$kf_online = trim($_POST['kf_online']);
    	$kf_status = trim($_POST['kf_status']);
    	$kf_safety = trim($_POST['kf_safety']);
    	$kf_id = trim($_POST['kf_id']);
    	
        // 过滤参数
        if(empty($kf_title) || $kf_title == '' || $kf_title == null || !isset($kf_title)){
            
            $result = array(
			    'code' => 203,
                'msg' => '标题未填写'
		    );
        }else if(empty($kf_rkym) || $kf_rkym == '' || $kf_rkym == null || !isset($kf_rkym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '入口域名未选择'
		    );
        }else if(empty($kf_ldym) || $kf_ldym == '' || $kf_ldym == null || !isset($kf_ldym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '落地域名未选择'
		    );
        }else if(empty($kf_dlym) || $kf_dlym == '' || $kf_dlym == null || !isset($kf_dlym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '短链域名未选择'
		    );
        }else if(empty($kf_model) || $kf_model == '' || $kf_model == null || !isset($kf_model)){
            
            $result = array(
			    'code' => 203,
                'msg' => '循环模式未选择'
		    );
        }else if(empty($kf_online) || $kf_online == '' || $kf_online == null || !isset($kf_online)){
            
            $result = array(
			    'code' => 203,
                'msg' => '在线状态未设置'
		    );
        }else if(empty($kf_status) || $kf_status == '' || $kf_status == null || !isset($kf_status)){
            
            $result = array(
			    'code' => 203,
                'msg' => '客服状态未设置'
		    );
        }else if(empty($kf_safety) || $kf_safety == '' || $kf_safety == null || !isset($kf_safety)){
            
            $result = array(
			    'code' => 203,
                'msg' => '顶部扫码安全提示未设置'
		    );
        }else if(empty($kf_id) || $kf_id == '' || $kf_id == null || !isset($kf_id)){
            
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
        	
            // 验证当前要编辑的kf_id的发布者是否为当前登录的用户
            $getKfid = ['kf_id'=>$kf_id];
            $getKfidResult = $huoma_kf->find($getKfid);
            $kf_creat_user = json_decode(json_encode($getKfidResult))->kf_creat_user;
            
            // 判断操作结果
            if($kf_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 参数
                $updatekfData = [
                    'kf_title' => $kf_title,
                    'kf_status' => $kf_status,
                    'kf_rkym' => $kf_rkym,
                    'kf_ldym' => $kf_ldym,
                    'kf_dlym' => $kf_dlym,
                    'kf_model' => $kf_model,
                    'kf_online' => $kf_online,
                    'kf_safety' => $kf_safety,
                    'kf_beizhu' => $kf_beizhu
                ];
                
                // 更新条件
                $updatekfCondition = [
                    'kf_id' => $kf_id,
                    'kf_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $updateKf = $huoma_kf->update($updatekfCondition,$updatekfData);
                
                // 判断操作结果
                if($updateKf){
                    
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
            'msg' => '未登录或登录失效'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>