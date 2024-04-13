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
        $kf_id = trim($_GET['kf_id']);
        
        // 过滤参数
        if(empty($kf_id) || !isset($kf_id)){
            
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

        	// 查询当前kf_id的详情
            $getKfInfo = $db->set_table('huoma_kf')->find(['kf_id'=>$kf_id]);
            
            // 返回数据
            if($getKfInfo && $getKfInfo > 0){
                
                // 入口域名
                $kf_rkym = json_decode(json_encode($getKfInfo))->kf_rkym;
                
                // 短链域名
                $kf_dlym = json_decode(json_encode($getKfInfo))->kf_dlym;
                
                // 短链Key
                $kf_key = json_decode(json_encode($getKfInfo))->kf_key;
                
                // 生成longUrl
                $longUrl = dirname(dirname(dirname($kf_rkym.$_SERVER["REQUEST_URI"]))).'/common/kf/redirect/?kid='.$kf_id.'#'.base64_encode($kf_key);
                
                // 生成shortUrl
                $shortUrl = $kf_dlym.'/s/'.$kf_key;
                
                // 生成qrcodeUrl
                $qrcodeUrl = dirname(dirname(dirname($kf_rkym.$_SERVER["REQUEST_URI"]))).'/common/kf/redirect/?kid='.$kf_id.'#'.base64_encode(time());
                
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