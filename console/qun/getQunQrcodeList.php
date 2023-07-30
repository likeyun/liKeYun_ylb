<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     * 程序用途：获取群二维码列表
     * 最后维护日期：2023-06-03
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
        $qun_id = trim(intval($_GET['qun_id']));
        
        // 过滤参数
        if(empty($qun_id) || !isset($qun_id)){
            
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

            // 获取该qun_id的qun_title
            $getQunTitle = $db->set_table('huoma_qun')->find(['qun_id' => $qun_id]);
        
            // 获取群二维码列表
        	$getQunQrcodeList = $db->set_table('huoma_qun_zima')->findAll($conditions = ['qun_id' => $qun_id],$order = 'ID ASC');
            
            // 返回数据
            if($getQunQrcodeList && $getQunQrcodeList > 0){
                
                // 有结果
                $result = array(
        		    'qunQrcodeList' => $getQunQrcodeList,
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'qun_title' => json_decode(json_encode($getQunTitle))->qun_title
    		    );
            }else{
                
                // 无结果
                $result = array(
        		    'code' => 204,
        		    'msg' => '获取失败，原因：'.json_encode($getQunQrcodeList),
        		    'qun_title' => json_decode(json_encode($getQunTitle))->qun_title
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