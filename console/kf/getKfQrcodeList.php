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
        	
            // 数据库huoma_kf表
        	$huoma_kf = $db->set_table('huoma_kf');
        	
            // 获取该kf_id的kf_title
            $getKftitle = ['kf_id' => $kf_id];
            $getKftitleResult = $huoma_kf->find($getKftitle);
        
        	// 数据库huoma_kf_zima表
        	$huoma_kf_zima = $db->set_table('huoma_kf_zima');
        
            // 获取当前kf_id的客服子码，ASC排序
        	$getKfzmList = $huoma_kf_zima->findAll($conditions = ['kf_id' => $kf_id],$order = 'ID ASC');
            
            // 返回数据
            if($getKfzmList && $getKfzmList > 0){
                
                // 有结果
                $result = array(
        		    'kfQrcodeList' => $getKfzmList,
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'kf_title' => json_decode(json_encode($getKftitleResult))->kf_title // 标题，客服子码列表显示
    		    );
            }else{
                
                // 无结果
                $result = array(
        		    'code' => 204,
        		    'msg' => '获取失败，原因：'.json_encode($getKfzmList),
        		    'kf_title' => json_decode(json_encode($getKftitleResult))->kf_title // 标题，客服子码列表显示
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