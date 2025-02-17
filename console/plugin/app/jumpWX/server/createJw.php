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
        $jw_xcx_appid = trim($_POST['jw_xcx_appid']);
        $jw_xcx_appsecret = trim($_POST['jw_xcx_appsecret']);
        $jw_xcx_path = trim($_POST['jw_xcx_path']);
        $jw_xcx_query = trim($_POST['jw_xcx_query']);
        $jw_xcx_urlscheme = trim($_POST['jw_xcx_urlscheme']);
        $jw_caoliaoqrcode = trim($_POST['jw_caoliaoqrcode']);
        $jw_jinshandoc = trim($_POST['jw_jinshandoc']);
        $jw_tencentdoc = trim($_POST['jw_tencentdoc']);
        $jw_workwxpan = trim($_POST['jw_workwxpan']);
        $jw_csdnblog = trim($_POST['jw_csdnblog']);
        $selectedTag = trim($_POST['selectedTag']);
        $jw_beizhu = trim($_POST['jw_beizhu']);
        $jw_platform = trim($_POST['jw_platform']);
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
                'msg' => '分享图未上传'
            );
        }else if(empty($jw_platform) || !isset($jw_platform)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择你要投放的平台'
            );
        }else if($selectedTag == 'urlscheme' && empty($jw_xcx_appid) && empty($jw_xcx_urlscheme)){
            
            $result = array(
                'code' => 203,
                'msg' => '小程序Appid未填写'
            );
        }else if($selectedTag == 'urlscheme' && empty($jw_xcx_appsecret) && empty($jw_xcx_urlscheme)){
            
            $result = array(
                'code' => 203,
                'msg' => '小程序AppSecret未填写'
            );
        }else if($selectedTag == 'urlscheme' && empty($jw_xcx_path) && empty($jw_xcx_urlscheme)){
            
            $result = array(
                'code' => 203,
                'msg' => '小程序路径未填写'
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
        }else if($selectedTag == 'csdn' && empty($jw_csdnblog)){
            
            $result = array(
                'code' => 203,
                'msg' => 'CSDN博客链接未填写'
            );
        }else{
            
            // 自建小程序Url Scheme
            if($selectedTag == 'urlscheme'){ 
                
                // 使用其他地方创建的URLScheme进行创建
                // 判断是否直接填了其他地方创建的URLScheme
                if($jw_xcx_urlscheme) {
                    
                    // 是的
                    if(strpos($jw_xcx_urlscheme,'weixin://') !== false) {
                        
                        // 验证是否符合规则
                        // 符合
                        // 赋值
                        $jw_url = $jw_xcx_urlscheme;
                        
                        // 目标链接原始内容
                        $jw_original_content = $jw_xcx_urlscheme;
                    }else {
                        
                        // 不符合
                        $result = array(
                            'code' => 201,
                            'msg' => '你填写的从其他地方生成的URLScheme不符合规则'
                        );
                        echo json_encode($result,JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }else {
                
                    // 使用自己填写的Appid进行创建
                    if($jw_xcx_appid && $jw_xcx_appsecret && $jw_xcx_path) {
                        
                        // 使用自建小程序
                        $response = createUrlScheme::generateScheme($jw_xcx_appid, $jw_xcx_appsecret, $jw_xcx_path, $jw_xcx_query, 'release');
                        
                        // 错误代码
                        $errcode = json_decode($response,true)['errcode'];
                        
                        // 错误信息
                        $errmsg = json_decode($response,true)['errmsg'];
                        
                        if($errcode == '40001') {
                            
                            // access_token生成失败
                            $result = array(
                                'code' => 201,
                                'msg' => 'access_token获取失败，原因是：' . $errmsg
                            );
                        }else {
                            
                            // access_token获取成功
                            // 开始创建
                            if($errcode == '0') {
                                
                                // 创建成功
                                $openlink = json_decode($response,true)['openlink'];
                                
                                // 验证这个是不是正常的生成结果
                                if(strpos($openlink,'weixin://') !== false) {
                                    
                                    // 是的
                                    // 赋值
                                    $jw_url = $openlink;
                                    
                                    // 目标链接原始内容
                                    $jw_original_content = $jw_xcx_path;
                                }else {
                                    
                                    // 生成失败
                                    $result = array(
                                        'code' => 201,
                                        'msg' => '生成失败，未知错误~'
                                    );
                                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                                    exit;
                                }
                            }else if ($errcode == '40165') {
                                
                                // 小程序路径不正确或不存在
                                $result = array(
                                    'code' => 201,
                                    'msg' => '小程序跳转路径不正确或不存在'
                                );
                                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                                exit;
                            }else if ($errcode == '40212') {
                                
                                // 小程序路径参数不规范
                                $result = array(
                                    'code' => 202,
                                    'msg' => '小程序路径参数不规范'
                                );
                                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                                exit;
                            }else {
                                
                                // 其它错误
                                $result = array(
                                    'code' => 203,
                                    'msg' => '创建时发生错误，错误原因：' . $response
                                );
                                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                                exit;
                            }
                        }
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
            }
            
            if($selectedTag == 'caoliaoqrcode'){ 
                
                // 草料二维码
                $result = array(
                    'code' => 201,
                    'msg' => '该渠道需付费购买，请点击 <a href="https://viusosibp88.feishu.cn/docx/Tot8dTJJIoDw4Px1nlsc6oH9n5g" target="_blank">https://viusosibp88.feishu.cn/docx/Tot8dTJJIoDw4Px1nlsc6oH9n5g</a> 前往付费插件购买页面。'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if($selectedTag == 'jinshandoc'){ 
                
                // 金山文档
                $result = array(
                    'code' => 201,
                    'msg' => '该渠道需付费购买，请点击 <a href="https://viusosibp88.feishu.cn/docx/Tot8dTJJIoDw4Px1nlsc6oH9n5g" target="_blank">https://viusosibp88.feishu.cn/docx/Tot8dTJJIoDw4Px1nlsc6oH9n5g</a> 前往付费插件购买页面。'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if($selectedTag == 'tencentdoc'){ 
                
                // 腾讯文档
                $result = array(
                    'code' => 201,
                    'msg' => '该渠道需付费购买，请点击 <a href="https://likeyunkeji.likeyunba.com/shop/?key=wailian" target="_blank">https://likeyunkeji.likeyunba.com/shop/?key=wailian</a> 前往付费插件购买页面。'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if($selectedTag == 'workwxpan'){ 
                
                // 企业微盘
                $result = array(
                    'code' => 201,
                    'msg' => '该渠道需付费购买，请点击 <a href="https://likeyunkeji.likeyunba.com/shop/?key=wailian" target="_blank">https://likeyunkeji.likeyunba.com/shop/?key=wailian</a> 前往付费插件购买页面。'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if($selectedTag == 'csdn'){ 
                
                // CSDN小程序
                $result = array(
                    'code' => 201,
                    'msg' => '该渠道需付费购买，请点击 <a href="https://likeyunkeji.likeyunba.com/shop/?key=wailian" target="_blank">https://likeyunkeji.likeyunba.com/shop/?key=wailian</a> 前往付费插件购买页面。'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // ID生成
            $jw_id = rand(101112,898989);
            
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
                'jw_url' => $jw_url,
                'jw_platform' => $jw_platform,
                'jw_original_content' => '-',
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
    
    // 自建小程序获取Url Scheme
    class createUrlScheme {
        
        // 静态方法用于获取access_token并发送POST请求
        public static function generateScheme($appid, $secret, $path, $query = "", $env_version = "release") {
            $accessToken = self::getAccessToken($appid, $secret);
    
            if ($accessToken !== null) {
                
                // 包含access_token的API端点URL
                $url = "https://api.weixin.qq.com/wxa/generatescheme?access_token=$accessToken";
    
                // POST请求的数据
                $data = [
                    "jump_wxa" => [
                        "path" => $path,
                        "query" => $query,
                        "env_version" => $env_version
                    ]
                ];
    
                // 将数据编码为JSON格式
                $jsonData = json_encode($data);
    
                // 初始化cURL会话
                $ch = curl_init($url);
    
                // 设置cURL选项
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ]);
    
                // 执行cURL请求并获取响应
                $response = curl_exec($ch);
    
                // 关闭cURL会话
                curl_close($ch);
    
                // 返回响应
                return $response;
            } else {
                return null;
            }
        }
    
        // 静态方法用于获取access_token
        private static function getAccessToken($appid, $secret) {
            $cacheFile = 'access_token.php';
    
            // 检查缓存文件是否存在且有效
            if (file_exists($cacheFile)) {
                $accessTokenData = include $cacheFile;
                if ($accessTokenData['expires_in'] > time()) {
                    return $accessTokenData['access_token'];
                }
            }
    
            // 如果缓存无效或已过期，获取新的token
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
    
            // 初始化cURL会话
            $ch = curl_init($url);
    
            // 设置cURL选项
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            // 执行cURL请求并获取响应
            $response = curl_exec($ch);
    
            // 关闭cURL会话
            curl_close($ch);
    
            // 解码JSON响应
            $data = json_decode($response, true);
    
            // 检查是否成功获取access_token
            if (isset($data['access_token'])) {
                
                // 将access_token和过期时间存储在PHP文件中
                $accessTokenData = [
                    'access_token' => $data['access_token'],
                    'expires_in' => time() + $data['expires_in']
                ];
                file_put_contents($cacheFile, '<?php return ' . var_export($accessTokenData, true) . ';');
                return $accessTokenData['access_token'];
            } else {
                
                // 获取access_token失败
                return array(
                    'code' => 203,
                    'msg' => "获取access_token失败。失败原因: " . $data['errmsg']
                );
            }
        }
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
