<?php
    
    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $jw_title = trim($_POST['jw_title']);
        $jw_dxccym = trim($_POST['jw_dxccym']);
        $jw_icon = trim($_POST['jw_icon']);
        $jw_bgimg = trim($_POST['jw_bgimg']);
        $jw_urlscheme = trim($_POST['jw_urlscheme']);
        $jw_caoliaoqrcode = trim($_POST['jw_caoliaoqrcode']);
        $jw_jinshandoc = trim($_POST['jw_jinshandoc']);
        $jw_tencentdoc = trim($_POST['jw_tencentdoc']);
        $jw_workwxpan = trim($_POST['jw_workwxpan']);
        $selectedTag = trim($_POST['selectedTag']);
        $jw_beizhu = trim($_POST['jw_beizhu']);
        $jw_create_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($jw_title) || !isset($jw_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($jw_dxccym) || !isset($jw_dxccym)){
            
            $result = array(
                'code' => 203,
                'msg' => '域名未选择'
            );
        }else if(empty($jw_icon) || !isset($jw_icon)){
            
            $result = array(
                'code' => 203,
                'msg' => '图标未上传'
            );
        }else if(empty($jw_bgimg) || !isset($jw_bgimg)){
            
            $result = array(
                'code' => 203,
                'msg' => '背景图片未上传'
            );
        }else if($selectedTag == 'urlscheme' && empty($jw_urlscheme)){
            
            $result = array(
                'code' => 203,
                'msg' => 'Url Scheme链接未填写'
            );
        }else if($selectedTag == 'caoliaoqrcode' && empty($jw_caoliaoqrcode)){
            
            $result = array(
                'code' => 203,
                'msg' => '公众号嵌入链接未填写'
            );
        }else if($selectedTag == 'jinshandoc' && empty($jw_jinshandoc)){
            
            $result = array(
                'code' => 203,
                'msg' => '金山文档链接未填写'
            );
        }else if($selectedTag == 'tencentdoc' && empty($jw_tencentdoc)){
            
            $result = array(
                'code' => 203,
                'msg' => '腾讯文档链接未填写'
            );
        }else if($selectedTag == 'workwxpan' && empty($jw_workwxpan)){
            
            $result = array(
                'code' => 203,
                'msg' => '企业微盘链接未填写'
            );
        }else{
            
            if($selectedTag == 'urlscheme'){ 
                
                // Url Scheme
                if(strpos($jw_urlscheme,'weixin://') !== false) {
                    
                    // 成功
                    $jw_url = $jw_urlscheme;
                }else {
                    
                    // Url Scheme链接不符合规范
                    $result = array(
                        'code' => 201,
                        'msg' => 'Url Scheme链接不符合规范'
                    );
                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            if($selectedTag == 'caoliaoqrcode'){ 
                
                // 收费功能
                $result = array(
                    'code' => 101,
                    'msg' => '该功能为收费功能，如需使用，请购买源码后使用。收费168元，请联系微信号：',
                    'buy_link' => 'sansure2016'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if($selectedTag == 'jinshandoc'){ 
                
                // 收费功能
                $result = array(
                    'code' => 101,
                    'msg' => '该功能为收费功能，如需使用，请购买源码后使用。收费168元，请联系微信号：',
                    'buy_link' => 'sansure2016'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if($selectedTag == 'tencentdoc'){ 
                
                // 收费功能
                $result = array(
                    'code' => 101,
                    'msg' => '该功能为收费功能，如需使用，请购买源码后使用。收费168元，请联系微信号：',
                    'buy_link' => 'sansure2016'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if($selectedTag == 'workwxpan'){ 
                
                // 收费功能
                $result = array(
                    'code' => 101,
                    'msg' => '该功能为收费功能，如需使用，请购买源码后使用。收费168元，请联系微信号：',
                    'buy_link' => 'sansure2016'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // ID生成
            $jw_id = rand(100000,999999);
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // jw_token
            $jw_token = MD5($jw_id . $jw_title . $jw_url . $jw_create_user);

        	// 参数
            $createJw = [
                'jw_id' => $jw_id,
                'jw_title' => $jw_title,
                'jw_dxccym' => $jw_dxccym,
                'jw_icon' => $jw_icon,
                'jw_bgimg' => $jw_bgimg,
                'jw_url' => $jw_url,
                'jw_beizhu' => $jw_beizhu,
                'jw_create_user' => $jw_create_user,
                'jw_token' => $jw_token
            ];
            
            // 执行SQL
            $createJwSQL = $db->set_table('ylb_jumpWX')->add($createJw);
            
            // 执行结果
            if($createJwSQL){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'msg' => '创建成功'
                );
            }else{
                
                // 失败
                $result = array(
                    'code' => 202,
                    'msg' => '创建失败'
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
    
    // cUrlPost
    function cUrlPost($url, $params)
    {
        $url .= '?' . http_build_query($params);
    
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36',
            ],
        ]);
        $curl_result = curl_exec($ch);
        if ($curl_result === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            return json_encode(['code' => 500, 'msg' => "cURL Error: $error (Code: $errno)"]);
        }
        curl_close($ch);
        return $curl_result;
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
