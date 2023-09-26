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
    	$tkl = trim($_GET['tkl']);
    	
        // 过滤参数
        if(empty($tkl) || !isset($tkl)){
            
            $result = array(
			    'code' => 203,
                'msg' => '淘口令为空'
		    );
        }else{
            
            // 获取配置
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
        
        	// 获取当前登录的用户的中间页配置
            $getThisUserZjyConfig = $db->set_table('huoma_tbk_config')->find(['zjy_config_user'=>$LoginUser]);
            
            // 获取结果
            if($getThisUserZjyConfig && $getThisUserZjyConfig > 0){
                
                $appkey = json_decode(json_encode($getThisUserZjyConfig))->zjy_config_appkey;
                $sid = json_decode(json_encode($getThisUserZjyConfig))->zjy_config_sid;
                $pid = json_decode(json_encode($getThisUserZjyConfig))->zjy_config_pid;
                
                // $appkey未配置
                if(empty($appkey) || !isset($appkey) || $appkey == '未设置'){
                    
                    $result = array(
                        'code' => 202,
                        'msg' => '未配置appkey'
                    );
                }else if(empty($sid) || !isset($sid) || $sid == '未设置'){
                    
                    $result = array(
                        'code' => 202,
                        'msg' => '未配置sid'
                    );
                }else if(empty($pid) || !isset($pid) || $pid == '未设置'){
                    
                    $result = array(
                        'code' => 202,
                        'msg' => '未配置pid'
                    );
                }else{
                    
                    // 请求接口
                    $reqResult = reqApi($appkey,$sid,$pid,urlencode($tkl));
                    
                    // 解析JSON
                    $code = json_decode($reqResult,true)["status"]; // 状态码
                    
                    if($code == 200){
                        
                        $explainJson = json_decode($reqResult,true)["content"][0];
                        $long_title = $explainJson['tao_title']; // 长标题
                        $short_title = mb_substr($long_title,0,13,'utf-8');// 短标题（通过截取长标题前13个字实现）
                        $yprice = $explainJson['size']; // 原价
                        $qhprice = $explainJson['quanhou_jiage']; // 券后价
                        $youhuiquan = $yprice-$qhprice; // 优惠券价格
                        $picUrl = $explainJson['small_images']; // 主图地址
                        $mytkl = $explainJson['tkl'];  // 淘口令
                        $shorturl2 = $explainJson['shorturl2']; // 微信跳转淘宝APP的链接
                        
                        // 券后价格式化
						if(strpos($qhprice,'.') !==false){
						    
							// 如果包含小数点，就要在最后面加一个0
							$qhprice = $qhprice."0";
						}else{
						    
							// 不包含小数点，就要在最后面加.00
							$qhprice = $qhprice.".00";
						}
                        
                        // 解析成功
                        $result = array(
                            'code' => $code,
                            'msg' => '解析成功',
                            'zjy_short_title' => $short_title,
                            'zjy_long_title' => $long_title,
                            'zjy_original_cost' => $yprice,
                            'zjy_discounted_price' => $qhprice,
                            'zjy_goods_img' => $picUrl,
                            'zjy_tkl' => $mytkl,
                            'zjy_goods_link' => $shorturl2
                        );
                    }else{
                        
                        // 解析失败
                        $result = array(
                            'code' => $code,
                            'msg' => '解析失败！原因：'.json_decode($reqResult,true)["content"]
                        );
                    }
                }
            }else{
                
                // 未配置接口或请求接口失败
                $result = array(
                    'code' => 202,
                    'msg' => '未配置接口或请求接口失败'
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
    
    // 请求接口
    function reqApi($appkey,$sid,$pid,$tkl){
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.zhetaoke.com:10001/api/open_gaoyongzhuanlian_tkl.ashx?appkey=".$appkey."&sid=".$sid."&pid=".$pid."&tkl=".$tkl."&signurl=5");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_result = curl_exec($ch);
        return $curl_result;
        curl_close($ch);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>