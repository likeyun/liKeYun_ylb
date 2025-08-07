<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $data_id = trim(intval($_GET['data_id']));
        
        // 过滤参数
        if(empty($data_id) || !isset($data_id)){
            
            // 非法请求
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 获取详情
            $getDataInfo = $db->set_table('ylbPlugin_wxdmQk')->find(['data_id' => $data_id]);
            
            // 返回数据
            if($getDataInfo){
                
                // 对象存储域名
                $data_dxccym = $getDataInfo['data_dxccym'];
                
                // Key
                $data_key = $getDataInfo['data_key'];
                
                // 拼接链接
                // $shareUrl = $data_dxccym.'?key='.$data_key;
                
                // 判断对象存储域名的类型
                // 根据类型决定参数的拼接方式
                if (preg_match('/\.(html|htm|svg|xhtml|xhtm|xml|png|jpg|jpeg|bmp|shtml)$/i', $data_dxccym)) {
                    
                    // 如果有文件后缀名作为结尾
                    // 使用?拼接参数
                    $shareUrl = $data_dxccym . '?key='.$data_key;
                }else {
                    
                    // 其他情况一般都是代表有多个参数
                    // 使用&拼接参数
                    $shareUrl = $data_dxccym . '&key='.$data_key;
                }
                
                // 返回数据
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'shareUrl' => $shareUrl.'#wechat_redirect',
        		    'qrcodeUrl' => $shareUrl.'#qrcode'
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