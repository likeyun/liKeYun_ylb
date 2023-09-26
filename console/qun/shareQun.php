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
        $qun_id = trim($_GET['qun_id']);
        
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
        
        	// 查询当前qun_id的详情
            $getQunInfo = $db->set_table('huoma_qun')->find(['qun_id'=>$qun_id]);
            
            // 返回数据
            if($getQunInfo){
                
                // 入口域名
                $qun_rkym = json_decode(json_encode($getQunInfo))->qun_rkym;
               
                // 短链域名
                $qun_dlym = json_decode(json_encode($getQunInfo))->qun_dlym;
               
                // 短链Key
                $qun_key = json_decode(json_encode($getQunInfo))->qun_key;
                
                // 生成longUrl
                $longUrl = dirname(dirname(dirname($qun_rkym.$_SERVER["REQUEST_URI"]))).'/common/qun/redirect/?qid='.$qun_id;
                
                // 生成shortUrl
                $shortUrl = $qun_dlym.'/s/'.$qun_key;
                
                // 生成qrcodeUrl
                $qrcodeUrl = dirname(dirname(dirname($qun_rkym.$_SERVER["REQUEST_URI"]))).'/common/qun/redirect/?qid='.$qun_id.'&t='.time();
                
                // 有结果
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'longUrl' => $longUrl,
        		    'shortUrl' => $shortUrl,
        		    'qrcodeUrl' => $qrcodeUrl
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