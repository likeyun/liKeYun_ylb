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
        $shareCard_id = trim($_GET['shareCard_id']);
        
        // 过滤参数
        if(empty($shareCard_id) || !isset($shareCard_id)){
            
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
        	
            // 获取详情
            $getshareCardInfoResult = $db->set_table('huoma_shareCard')->find(['shareCard_id'=>$shareCard_id]);
            
            // 返回数据
            if($getshareCardInfoResult){
                
                // 落地域名
                $shareCard_ldym = json_decode(json_encode($getshareCardInfoResult))->shareCard_ldym;
                
                // 生成longUrl
                $longUrl = dirname(dirname(dirname($shareCard_ldym.$_SERVER["REQUEST_URI"]))).'/common/shareCard/redirect/?sid='.$shareCard_id;
                
                // 有结果
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'longUrl' => $longUrl
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
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>