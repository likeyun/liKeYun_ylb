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
  
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 今天的日期
        $todayDate = date('Y-m-d');
    	
        // 昨天的日期
        $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
    	
        // 获取今天的IP
        $getTodayIPNum = $db->set_table('huoma_ip')->findAll(['ip_create_time'=>$todayDate]);
        
        // 获取昨天的IP
        $yesTerdayIPNum = $db->set_table('huoma_ip')->findAll(['ip_create_time'=>$yesterdayDate]);
        
        // 初始值
        $initVal = array(
            array(
                'qun_ip'=>0,
                'kf_ip'=>0,
                'channel_ip'=>0,
                'dwz_ip'=>0,
                'zjy_ip'=>0,
                'shareCard_ip'=>0,
                'multiSPA_ip'=>0,
            )
        );
        
        // 需要分情况获取
        if($getTodayIPNum && $yesTerdayIPNum){
            
            // 1 今天有、昨天有
            $result = array(
			    'code' => 200,
                'msg' => '获取成功（今天有、昨天有）',
                'todayIP' => $getTodayIPNum,
                'yesterdayIP' => $yesTerdayIPNum
		    );
        }else if($getTodayIPNum){
            
            // 2 今天有、昨天无
            $result = array(
			    'code' => 200,
                'msg' => '获取成功（今天有、昨天无）',
                'todayIP' => $getTodayIPNum,
                'yesterdayIP' => $initVal
		    );
        }else if($yesTerdayIPNum){
            
            // 3 今天无、昨天有
            $result = array(
			    'code' => 200,
                'msg' => '获取成功（今天无、昨天有）',
                'todayIP' => $initVal,
                'yesterdayIP' => $yesTerdayIPNum
		    );
        }else{
            
            // 4 今天无、昨天无
            $result = array(
			    'code' => 200,
                'msg' => '获取成功（今天无、昨天无）',
                'todayIP' => $initVal,
                'yesterdayIP' => $initVal
		    );
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