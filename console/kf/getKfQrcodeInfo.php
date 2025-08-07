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
        if(empty($zm_id) || $zm_id == '' || $zm_id == null || !isset($zm_id)){
            
            // 非法请求
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 数据库huoma_kf_zima表
        	$huoma_kf_zima = $db->set_table('huoma_kf_zima');
        
        	// 获取当前zm_id的详情
            // 操作条件
        	$getKfzmInfo = ['zm_id'=>$zm_id];
        	
            // 执行操作
            $getKfzmInfoResult = $huoma_kf_zima->find($getKfzmInfo);
            
            // 操作结果
            if($getKfzmInfoResult && $getKfzmInfoResult > 0){
                
                // 根据当前zm_id下的kf_id获取kf_model
                $huoma_kf = $db->set_table('huoma_kf');
                $getThisZmidKfidKfmodel = ['kf_id'=>json_decode(json_encode($getKfzmInfoResult))->kf_id];
                $getThisZmidKfidKfmodelResult = $huoma_kf->find($getThisZmidKfidKfmodel);
                
                // 有结果
                $result = array(
        		    'kfzmInfo' => $getKfzmInfoResult,
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'kf_model' => json_decode(json_encode($getThisZmidKfidKfmodelResult))->kf_model // 用于判断是否需要填写阈值
    		    );
            }else{
                
                // 无结果
                $result = array(
        		    'code' => 204,
        		    'msg' => '获取失败'
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