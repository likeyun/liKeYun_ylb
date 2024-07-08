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
                
                // 草料二维码
                // 开始生成草料二维码的Url Scheme
                if(strpos($jw_caoliaoqrcode,'qr61.cn') !== false) {
                    
                    // 在嵌入链接中提取caoliao_uid和caoliao_qid
                    $caoliao_uid_ = substr($jw_caoliaoqrcode, strripos($jw_caoliaoqrcode, "qr61.cn%2F") + 10);
                    $caoliao_uid = substr($caoliao_uid_, 0, strrpos($caoliao_uid_, "%2F"));
                    $caoliao_qid = substr($jw_caoliaoqrcode, strripos($jw_caoliaoqrcode, "qr61.cn%2F") + 19);
                    
                    // 验证是否截取到uid和qid
                    if (empty($caoliao_uid) || empty($caoliao_qid)) {
                        
                        // 提取失败
                        $result = array(
                            'code' => 201,
                            'msg' => '无法通过你的公众号嵌入链接提取到有效的参数，请检查你的公众号嵌入链接是否符合规则！'
                        );
                        echo json_encode($result,JSON_UNESCAPED_UNICODE);
                        exit;
                    }else {
                        
                        // 请求接口
                        $getTicketUrl = file_get_contents('https://nc.cli.im/api/weixin/getWxUrlScheme/?query=q=qr61.cn/'.$caoliao_uid.'/'.$caoliao_qid.'&path=pages/code/code&appid=wx5db79bd23a923e8e&org_coding='.$caoliao_uid);
                        
                        // 获取生成结果
                        $create_result = json_decode($getTicketUrl,true)['msg']['text'];
                        
                        // 验证结果
                        if($create_result == '微信UrlScheme创建失败') {
                            
                            // 返回失败的信息
                            $result = array(
                                'code' => 201,
                                'msg' => '草料二维码Url_Scheme生成失败！'
                            );
                            echo json_encode($result,JSON_UNESCAPED_UNICODE);
                            exit;
                        }else {
                            
                            // 生成成功
                            $fetchUrl = json_decode($getTicketUrl,true)['data']['wx_url_scheme']['fetchUrl'];
                            $fetchUrlData = file_get_contents($fetchUrl);
                            $urlScheme = json_decode($fetchUrlData,true)['data']['urlScheme'];
                            
                            // 赋值
                            $jw_url = $urlScheme;
                        }
                    }
                }else {
                    
                    // 公众号嵌入链接不符合规范
                    $result = array(
                        'code' => 201,
                        'msg' => '公众号嵌入链接不符合规范'
                    );
                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            if($selectedTag == 'jinshandoc'){ 
                
                // 金山文档
                // 开始生成金山文档的Url Scheme
                if(strpos($jw_jinshandoc,'kdocs.cn') !== false) {
                    
                    // 在url中截取sid
                    $jinshan_sid = substr($jw_jinshandoc, strripos($jw_jinshandoc, "kdocs.cn/l/") + 11);
                    
                    if($jinshan_sid) {
                        
                        // 请求参数
                        $curlParams = [
                            'appid' => 'wx5b97b0686831c076',
                            'path' => 'pages/navigate/navigate',
                            'query' => http_build_query([
                                'url' => 'pages/preview/preview?from=wxminiprogram&fid=&sid=' . $jinshan_sid . '&fname=',
                                'scene' => '102',
                                'jump_from' => 'wechatlogin_guide_passive',
                                'comp' => 'docx',
                                'dw' => '1',
                            ]),
                            'env_version' => 'release',
                            'is_expire' => 'true',
                            'expire_time' => time() + 7200,
                        ];
                        
                        // 获取wxurl
                        $wxaurlData = cUrlPost("https://account.kdocs.cn/api/v3/miniprogram/urllink", $curlParams);
                        
                        // 提取wxaurl
                        $response = json_decode($wxaurlData, true);
                        $result_wxaurl = $response['result'];
                        $url_link = $response['url_link'];
                        
                        // 验证生成结果
                        if ($result_wxaurl == 'ok') {
                            
                            // 生成成功
                            $wxaurlCode = basename($url_link);
                            
                            // 赋值
                            $jw_url = 'weixin://dl/business/?t='.$wxaurlCode;
                        } else {
                            
                            // 生成失败
                            $result = array(
                                'code' => 201,
                                'msg' => '生成失败，可能是金山文档API失效！'
                            );
                            echo json_encode($result,JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                    }else {
                        
                        // sid为空
                        // 无法从你提供的金山文档链接截取到符合要求的参数
                        $result = array(
                            'code' => 201,
                            'msg' => '无法从你提供的金山文档链接截取到符合要求的参数'
                        );
                        echo json_encode($result,JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }else {
                    
                    // 金山文档链接不符合规范
                    $result = array(
                        'code' => 201,
                        'msg' => '金山文档链接不符合规范'
                    );
                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            if($selectedTag == 'tencentdoc'){ 
                
                // 腾讯文档
                // 开始生成腾讯文档的Url Scheme
                if(strpos($jw_tencentdoc,'docs.qq.com') !== false) {
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://docs.qq.com/v2/user/wechat/urllink/generate');
                    curl_setopt($ch, CURLOPT_POST, true);
                    
                    // 请求参数
                    $Params = array(
                        'path' => 'pages/detail/detail',
                        'query' => 'url='.urlencode($jw_tencentdoc)
                    );
                    
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Params));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $headers[] = "user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.5414.121 Safari/537.36";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    
                    // 生成结果
                    $getTencentUrlScheme = curl_exec($ch);
                    $msg = json_decode($getTencentUrlScheme,true)['msg'];
                    
                    if($msg == 'success') {
                        
                        // 解析出urlLink
                        $urlLink = json_decode($getTencentUrlScheme,true)['result']['urlLink'];
                        
                        // 解析URL
                        $urlLink_parts = parse_url($urlLink);
                        
                        // 获取路径部分
                        $urlLink_path = $urlLink_parts["path"];
                        
                        // 将路径分割为段
                        $urlLink_segments = explode('/', $urlLink_path);
                        
                        // 最终获取到Url Scheme Token
                        $urlLink_Token = end($urlLink_segments);
                        
                        // 赋值
                        $jw_url = 'weixin://dl/business/?t=' . $urlLink_Token;
                    }else {
                        
                        // 生成失败
                        $result = array(
                            'code' => 201,
                            'msg' => '生成失败，应该是腾讯文档API的问题！'
                        );
                        echo json_encode($result,JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }else {
                    
                    // 腾讯文档链接不符合规范
                    $result = array(
                        'code' => 201,
                        'msg' => '腾讯文档链接不符合规范'
                    );
                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            if($selectedTag == 'workwxpan'){ 
                
                // 企业微盘
                // 开始生成企业微盘的Url Scheme
                if(strpos($jw_workwxpan,'drive.weixin.qq.com') !== false) {
                    
                    // 提取share_code
                    $share_code = substr($jw_workwxpan, strripos($jw_workwxpan, "s?k=") + 4);
                    
                    if($share_code) {
                        
                        // 初始化cURL会话
                        $ch = curl_init();
                        
                        // 设置请求的URL
                        $url = 'https://drive.weixin.qq.com/webdisk/jsapi';
                        curl_setopt($ch, CURLOPT_URL, $url);
                        
                        // 设置cURL选项，启用POST请求
                        curl_setopt($ch, CURLOPT_POST, true);
                        
                        // 设置POST字段
                        $data = array(
                            'func' => '2',
                            'query' => 'share_code=AGkAQwdRAAotwkfwrf'
                        );
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                        
                        // 返回响应而不是直接输出
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        
                        // 执行cURL请求并获取响应
                        $response = curl_exec($ch);
                        
                        // 直接解析出scheme
                        $scheme = json_decode($response,true)['body']['scheme'];
                        
                        // 验证生成结果
                        if(strpos($scheme,'weixin://') !== false) {
                            
                            // 生成成功
                            // 赋值
                            $jw_url = $scheme;
                        }else {
                            
                            // 生成失败
                            $result = array(
                                'code' => 201,
                                'msg' => '生成失败，应该是企业微信微盘API的问题！'
                            );
                            echo json_encode($result,JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                        
                        // 关闭cURL会话
                        curl_close($ch);
                    }else {
                        
                        // 无法从你提供的企业微信微盘链接截取到符合要求的参数
                        $result = array(
                            'code' => 201,
                            'msg' => '无法从你提供的企业微信微盘链接截取到符合要求的参数'
                        );
                        echo json_encode($result,JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }else {
                    
                    // 企业微盘分享链接不符合规范
                    $result = array(
                        'code' => 201,
                        'msg' => '企业微盘分享链接不符合规范'
                    );
                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                    exit;
                }
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