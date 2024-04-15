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
        $zjy_id = trim($_GET['zjy_id']);
        
        // 过滤参数
        if(empty($zjy_id) || !isset($zjy_id)){
            
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
            $getZjyInfoResult = $db->set_table('huoma_tbk')->find(['zjy_id'=>$zjy_id]);
            
            // 返回数据
            if($getZjyInfoResult && $getZjyInfoResult > 0){
                
                // 入口域名
                $zjy_rkym = json_decode(json_encode($getZjyInfoResult))->zjy_rkym;
                
                // 短链域名
                $zjy_dlym = json_decode(json_encode($getZjyInfoResult))->zjy_dlym;
                
                // 短链Key
                $zjy_key = json_decode(json_encode($getZjyInfoResult))->zjy_key;
                
                // 生成longUrl
                $longUrl = dirname(dirname(dirname($zjy_rkym.$_SERVER["REQUEST_URI"]))).'/common/zjy/redirect/?zid='.$zjy_id;
                
                // 生成shortUrl
                $shortUrl = $zjy_dlym.'/s/'.$zjy_key;
                
                // 有结果
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'longUrl' => $longUrl,
        		    'shortUrl' => $shortUrl,
        		    'qrcodeUrl' => $longUrl.'&t='.time()
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
            'msg' => '未登录或登录过期'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>