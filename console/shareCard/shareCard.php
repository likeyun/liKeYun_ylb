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
                
                // 模式
                $shareCard_model = $getshareCardInfoResult['shareCard_model'];
                
                // 目标链接
                $shareCard_url = $getshareCardInfoResult['shareCard_url'];
                
                if($shareCard_model == '1') {
                    
                    // 测试号
                    $longUrl = dirname(dirname(dirname($shareCard_ldym.$_SERVER["REQUEST_URI"]))).'/common/shareCard/redirect/?sid='.$shareCard_id;
                    $scanTips = '请使用已关注测试号的微信扫码';
                }else if($shareCard_model == '2') {
                    
                    // 认证号
                    // shareCard_url是你自己开发的页面
                    // 需要让自己的页面接收sid
                    $longUrl = addSidToURL($shareCard_url, $shareCard_id);
                    $scanTips = '请使用微信扫码';
                }else if($shareCard_model == '3') {
                    
                    // Safari分享
                    $longUrl = dirname(dirname(dirname($shareCard_ldym.$_SERVER["REQUEST_URI"]))).'/common/shareCard/Safari/?sid='.$shareCard_id;
                    $scanTips = '请使用iPhone手机的相机扫码';
                }
                
                // 有结果
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'longUrl' => $longUrl,
        		    'scanTips' => $scanTips
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
    
    // 用于拼接参数
    function addSidToURL($originalURL, $sid) {
        
        // 解析原始URL的查询参数
        $parsedURL = parse_url($originalURL);
    
        // 检查原始URL是否已经包含查询参数
        if (isset($parsedURL['query'])) {
            
            // 如果已经有查询参数，使用&作为连接符
            $separator = '&';
        } else {
            
            // 如果没有查询参数，使用?作为连接符
            $separator = '?';
        }
    
        // 使用http_build_query构建新的查询参数字符串
        $newQuery = http_build_query(array('sid' => $sid));
    
        // 将新的查询参数添加到原始URL
        $newURL = $originalURL . $separator . $newQuery;
    
        return $newURL;
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>