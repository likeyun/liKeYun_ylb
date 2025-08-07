<?php

    // 当前版本：2.4.1
    // 维护时间：2025-06-22
    
	// 编码
	header("Content-type:application/json");
	
	// 登录会话
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
        $jw_txym = trim($_POST['jw_txym']);
        $jw_caoliaoqrcode = trim($_POST['jw_caoliaoqrcode']);
        $jw_jinshandoc = trim($_POST['jw_jinshandoc']);
        $jw_tencentdoc = trim($_POST['jw_tencentdoc']);
        $jw_qywx = trim($_POST['jw_qywx']);
        $jw_h5page = trim($_POST['jw_h5page']);
        $jw_qqgroup = trim($_POST['jw_qqgroup']);
        $selectedTag = trim($_POST['selectedTag']);
        $jw_beizhu = trim($_POST['jw_beizhu']);
        $jw_platform = trim($_POST['jw_platform']);

        // 2.3.0新增
        $jw_jdjz = trim($_POST['jw_jdjz']);
        $jw_qqfriend = trim($_POST['jw_qqfriend']);
        
        // 2.4.0新增
        $jw_txwj = trim($_POST['jw_txwj']);
        $jw_zhaopin = trim($_POST['jw_zhaopin']);
        $jw_common_landpage = trim($_POST['jw_common_landpage']);
        $jw_douyin_landpage = trim($_POST['jw_douyin_landpage']);

        // 创建者
        $jw_create_user = trim($_SESSION["yinliubao"]);

        // 格式化日期时间
        $jw_expire_time = new DateTime(trim($_POST['jw_expire_time']));
        $jw_expire_time = $jw_expire_time->format("Y-m-d H:i:s");

        // 过滤参数
        if(empty($jw_title) || !isset($jw_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($jw_common_landpage) || !isset($jw_common_landpage)){
            
            $result = array(
                'code' => 203,
                'msg' => '通用落地页未选择'
            );
        }else if(empty($jw_douyin_landpage) || !isset($jw_douyin_landpage)){
            
            $result = array(
                'code' => 203,
                'msg' => '抖音落地页未选择'
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
        }else if($selectedTag == 'tencentym' && empty($jw_txym)){
            
            $result = array(
                'code' => 203,
                'msg' => '腾讯优码二维码链接未填写'
            );
        }else if($selectedTag == 'tencentwj' && empty($jw_txwj)){
            
            $result = array(
                'code' => 203,
                'msg' => '兔小巢团队博客链接未填写'
            );
        }else if($selectedTag == 'jdjz' && empty($jw_jdjz)){
            
            $result = array(
                'code' => 203,
                'msg' => '二维码图片链接未填写'
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
        }else if($selectedTag == 'zhaopin' && empty($jw_zhaopin)){
            
            $result = array(
                'code' => 203,
                'msg' => 'mypics.zhaopin.com图片链接未填写'
            );
        }else if($selectedTag == 'qywx' && empty($jw_qywx)){
            
            $result = array(
                'code' => 203,
                'msg' => '企业微信链接未填写'
            );
        }else if($selectedTag == 'qqgroup' && empty($jw_qqgroup)){
            
            $result = array(
                'code' => 203,
                'msg' => 'QQ群进群链接未填写'
            );
        }else if($selectedTag == 'qqfriend' && empty($jw_qqfriend)){
            
            $result = array(
                'code' => 203,
                'msg' => 'QQ二维码分享链接未填写'
            );
        }else if($selectedTag == 'h5page' && empty($jw_h5page)){
            
            $result = array(
                'code' => 203,
                'msg' => 'h5页面链接未填写'
            );
        }else{
            
            // 自建小程序URLScheme
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
                        
                        // ID生成
                        $jw_id = '10' . mt_rand(1000,9999);
                        
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
                            'jw_common_landpage' => $jw_common_landpage,
                            'jw_douyin_landpage' => $jw_douyin_landpage,
                            'jw_icon' => $jw_icon,
                            'jw_url' => $jw_url,
                            'jw_platform' => $jw_platform,
                            'jw_original_content' => $jw_original_content,
                            'jw_beizhu' => $jw_beizhu,
                            'jw_create_user' => $jw_create_user,
                            'jw_token' => $jw_token,
                            'jw_key' => createKey(5),
                            'jw_expire_time' => $jw_expire_time
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
                    }else {
                        
                        // 不符合
                        $result = array(
                            'code' => 201,
                            'msg' => '你填写的从其他地方生成的URLScheme不符合规则'
                        );
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
                                    
                                    // ID生成
                                    $jw_id = '10' . mt_rand(1000,9999);
                                    
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
                                        'jw_common_landpage' => $jw_common_landpage,
                                        'jw_douyin_landpage' => $jw_douyin_landpage,
                                        'jw_icon' => $jw_icon,
                                        'jw_url' => $jw_url,
                                        'jw_platform' => $jw_platform,
                                        'jw_original_content' => $jw_original_content,
                                        'jw_beizhu' => $jw_beizhu,
                                        'jw_create_user' => $jw_create_user,
                                        'jw_token' => $jw_token,
                                        'jw_key' => createKey(5),
                                        'jw_expire_time' => $jw_expire_time
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
                                }else {
                                    
                                    // 生成失败
                                    $result = array(
                                        'code' => 201,
                                        'msg' => '生成失败，未知错误~'
                                    );
                                }
                            }else if ($errcode == '40165') {
                                
                                // 小程序路径不正确或不存在
                                $result = array(
                                    'code' => 201,
                                    'msg' => '小程序跳转路径不正确或不存在'
                                );
                            }else if ($errcode == '40212') {
                                
                                // 小程序路径参数不规范
                                $result = array(
                                    'code' => 202,
                                    'msg' => '小程序路径参数不规范'
                                );
                            }else {
                                
                                // 其它错误
                                $result = array(
                                    'code' => 203,
                                    'msg' => '创建时发生错误，错误原因：' . $response
                                );
                            }
                        }
                    }else {
                        
                        // URLScheme链接不符合规范
                        $result = array(
                            'code' => 201,
                            'msg' => 'URLScheme链接不符合规范'
                        );
                    }
                }
            }else {
                
                // 其它跳转目标
                $result = array(
                    'code' => 201,
                    'msg' => '该跳转目标需付费购买正式版插才可使用，购买地址：<a href="https://viusosibp88.feishu.cn/docx/McHpd19OOoiVZtxyejjcUGkcnSe">https://viusosibp88.feishu.cn/docx/McHpd19OOoiVZtxyejjcUGkcnSe</a>'
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
    
    // 自建小程序获取URLScheme
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
    
    // URL验证
    function is_url($url){
        $r = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
        if(preg_match($r,$url)){
            return true;
        }else{
            return false;
        }
    }
    
    // 生成随机字符串
    function createKey($length){
        $str = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 
        'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's', 
        't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D', 
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', 
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $keys = array_rand($str, $length); 
        $password = '';
        for($i = 0; $i < $length; $i++){
            $password .= $str[$keys[$i]];
        }
        return $password;
    }
    
    // 生成 x-zp-page-request-id
    function generate_hex_id() {
        return bin2hex(random_bytes(16));
    }
    
    // 生成at和rt
    function generate_token_with_prefix($prefix = '', $useTime = true) {
        $random = bin2hex(random_bytes(16));
        $timestamp = $useTime ? time() : '';
        return $prefix . $timestamp . $random;
    }
    
    // 生成 x-zp-client-id
    function generate_uuid_v4() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>