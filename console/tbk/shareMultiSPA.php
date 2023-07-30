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
        $MultiSPA_id = trim($_GET['MultiSPA_id']);
        
        // 过滤参数
        if(empty($MultiSPA_id) || !isset($MultiSPA_id)){
            
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
            $getMultiSPAInfo = $db->set_table('huoma_tbk_mutiSPA')->find(['multiSPA_id'=>$MultiSPA_id]);
            
            // 返回数据
            if($getMultiSPAInfo){
                
                // 入口域名
                $multiSPA_rkym = json_decode(json_encode($getMultiSPAInfo))->multiSPA_rkym;
                
                // 短链域名
                $multiSPA_dlym = json_decode(json_encode($getMultiSPAInfo))->multiSPA_dlym;
                
                // 短链Key
                $multiSPA_key = json_decode(json_encode($getMultiSPAInfo))->multiSPA_key;
                
                // 生成longUrl
                $longUrl = dirname(dirname(dirname($multiSPA_rkym.$_SERVER["REQUEST_URI"]))).'/common/multiSPA/redirect/?mid='.$MultiSPA_id;
                
                // 生成shortUrl
                $shortUrl = $multiSPA_dlym.'/s/'.$multiSPA_key;
                
                // 有结果
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'longUrl' => $longUrl,
        		    'shortUrl' => $shortUrl
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
            'msg' => '未登'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>