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
	
    // 接收JSON数据
	$JsonData = trim(file_get_contents('php://input'));

    // 解析JSON数据
    $dwz_title = json_decode($JsonData)->dwz_title;
    $dwz_dlws = json_decode($JsonData)->dwz_dlws;
    $dwz_type = json_decode($JsonData)->dwz_type;
    $dwz_url = json_decode($JsonData)->dwz_url;
    $api_key = json_decode($JsonData)->api_key;
    $sign = json_decode($JsonData)->sign;

    // 固定参数
    // 管理员需进行设置
    // 设置后即作为用户请求Api生成短网址使用的域名
    // -----------------------
    $dwz_rkym = 'https://ylb.wxpad.cn'; // 入口域名
    $dwz_zzym = 'https://ylb.wxpad.cn'; // 中转域名
    $dwz_dlym = 'https://1-url.cn'; // 短链域名
    
    // 数据库配置
	include '../../Db.php';

	// 实例化类
	$db = new DB_API($config);
	
	// sql防注入
    if(preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$dwz_title)){
        
        $result = array(
	        'code' => 203,
            'msg' => '你输入的标题可能包含了一些不安全字符'
        );
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }else if(preg_match("/(and|or|select|update|drop|DROP|insert|create|delete|like|where|join|script|set)/i",$dwz_title)){
        
        $result = array(
	        'code' => 203,
            'msg' => '你输入的标题可能包含了一些不安全字符'
        );
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 过滤参数
    if(empty($dwz_title) || !isset($dwz_title)){
        
        $result = array(
            'code' => 203,
            'msg' => 'dwz_title参数为空'
        );
    }else if(empty($dwz_rkym) || !isset($dwz_rkym)){
        
        $result = array(
            'code' => 203,
            'msg' => '入口域名未设置，请打开 console/dwz/create/index.php 第29行 进行配置'
        );
    }else if(empty($dwz_zzym) || !isset($dwz_zzym)){
        
        $result = array(
            'code' => 203,
            'msg' => '中转域名未设置，请打开 console/dwz/create/index.php 第30行 进行配置'
        );
    }else if(empty($dwz_dlym) || !isset($dwz_dlym)){
        
        $result = array(
            'code' => 203,
            'msg' => '短链域名未设置，请打开 console/dwz/create/index.php 第31行 进行配置'
        );
    }else if(empty($dwz_dlws) || !isset($dwz_dlws)){
        
        $result = array(
            'code' => 203,
            'msg' => 'dwz_dlws参数为空'
        );
    }else if(empty($dwz_type) || !isset($dwz_type)){
        
        $result = array(
            'code' => 203,
            'msg' => 'dwz_type参数为空'
        );
    }else if(empty($dwz_url) || !isset($dwz_url)){
        
        $result = array(
            'code' => 203,
            'msg' => 'dwz_url参数为空'
        );
    }else if(is_url($dwz_url) == false){
        
        $result = array(
            'code' => 203,
            'msg' => '你输入的URL不符合格式'
        );
    }else{
        
        // 验证api_key
        $checkApiKey = $db->set_table('huoma_dwz_apikey')->find(['apikey'=>$api_key]);
        if($checkApiKey){
            
            // api_key存在
            $apikey_expire = json_decode(json_encode($checkApiKey))->apikey_expire;
            $apikey_status = json_decode(json_encode($checkApiKey))->apikey_status;
            $apikey_ip = json_decode(json_encode($checkApiKey))->apikey_ip;
            $apikey_quota = json_decode(json_encode($checkApiKey))->apikey_quota;
            $apikey_num = json_decode(json_encode($checkApiKey))->apikey_num;
            
            // 通过API来创建的创建者
            // 使用的是ApiKey用户名
            $apikey_user = json_decode(json_encode($checkApiKey))->apikey_user;

            global $apikey_secrete;
            $apikey_secrete = json_decode(json_encode($checkApiKey))->apikey_secrete;
            
            // 验证签名
            $apiSign = getSignature($dwz_title,$dwz_type,$dwz_url,$api_key,$apikey_secrete);
            
            // 今天的日期时间戳
            $thisDate = strtotime(date('Y-m-d H:i:s'));
            
            // 到期日期时间戳
            $expireDate = strtotime($apikey_expire);
            
            // 判断api_key是否已到期
            if($thisDate < $expireDate){
                
                // 未到期
                // 判断状态
                if($apikey_status == 1){
                    
                    // 可用
                    // 判断ip
                    if(!empty($apikey_ip)){
                        
                        // 需验证IP白名单
                        if($_SERVER['REMOTE_ADDR'] == $apikey_ip){
                            
                            // IP地址符合
                            // 创建短网址
                            createDwz($apikey_quota,$apikey_num,$sign,$apiSign,$dwz_title,$dwz_rkym,$dwz_zzym,$dwz_dlym,$dwz_type,$dwz_url,$apikey_user,$dwz_dlws,$api_key,$db);
                        }else{
                            
                            // IP地址不符合
                            $result = array(
                                'code' => 202,
                                'msg' => 'IP地址不在白名单'
                            );
                        }
                    }else{
                        
                        // 无需验证IP白名单
                        // 创建短网址
                        createDwz($apikey_quota,$apikey_num,$sign,$apiSign,$dwz_title,$dwz_rkym,$dwz_zzym,$dwz_dlym,$dwz_type,$dwz_url,$apikey_user,$dwz_dlws,$api_key,$db);
                    }
                }else{
                    
                    // 停用
                    $result = array(
                        'code' => 202,
                        'msg' => 'ApiKey已被管理员停用'
                    );
                }
            }else{
                
                // 已到期
                $result = array(
                    'code' => 202,
                    'msg' => 'ApiKey已到期'
                );
            }
        }else{
            
            // api_key不存在
            $result = array(
                'code' => 202,
                'msg' => 'ApiKey不存在'
            );
        }
    }
    
    // 创建短网址
    function createDwz($apikey_quota,$apikey_num,$sign,$apiSign,$dwz_title,$dwz_rkym,$dwz_zzym,$dwz_dlym,$dwz_type,$dwz_url,$apikey_user,$dwz_dlws,$api_key,$db){
        
        global $result;
        
        // 验证是否还有请求额度
        if($apikey_quota > $apikey_num){
            
            // 有额度
            // 验证签名
            if($sign == $apiSign){
                
                // 签名正确
                // 开始创建
                // 参数
                $dwzKey = creatKey($dwz_dlws);
                $creatdwz = [
                    'dwz_title'=>$dwz_title,
                    'dwz_rkym'=>$dwz_rkym,
                    'dwz_zzym'=>$dwz_zzym,
                    'dwz_dlym'=>$dwz_dlym,
                    'dwz_type'=>$dwz_type,
                    'dwz_url'=>$dwz_url,
                    'dwz_creat_user'=>$apikey_user,
                    'dwz_key' => $dwzKey,
                    'dwz_id'=>rand(100000,999999)
                ];
                
                // 执行SQL
                $creatdwzResult = $db->set_table('huoma_dwz')->add($creatdwz);
                
                // 判断执行结果
                if($creatdwzResult){
                    
                    // 更新请求次数
                    $apikey_num = $apikey_num+1;
                    $updateApikeyNumResult = $db->set_table('huoma_dwz_apikey')->update(['apikey'=>$api_key],['apikey_num'=>$apikey_num]);
                    
                    if($updateApikeyNumResult){
                        
                        // 成功
                        $result = array(
                            'code' => 200,
                            'msg' => '创建成功',
                            'url' => $dwz_dlym.'/'.$dwzKey
                        );
                    }else{
                        
                        // 请求次数更新失败
                        $result = array(
                            'code' => 200,
                            'msg' => '创建成功，但请求次数更新失败！错误代码所在位置：console/dwz/create/index.php第229行'
                        );
                    }
                    
                }else{
                    
                    // 失败
                    $result = array(
                        'code' => 202,
                        'msg' => '创建失败！'
                    );
                }
                
            }else{
                
                // 签名不正确
                $result = array(
                    'code' => 202,
                    'msg' => '签名不正确'
                );
            }
        }else{
            
            // 无额度
            $result = array(
                'code' => 202,
                'msg' => '创建额度已用完'
            );
        }
    }
    
    // 签名算法
    function getSignature($dwz_title,$dwz_type,$dwz_url,$api_key,$apikey_secrete){
        
        // dwz_title、dwz_type、dwz_url、api_key、api_secrete
        // 按顺序连接后进行MD5加密
        return MD5($dwz_title.$dwz_type.$dwz_url.$api_key.$apikey_secrete);
    }
    
    // 随机生成dwz_key
    function creatKey($length){
        $keyMember = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
        $keyStr = str_shuffle($keyMember);
        $keys = substr($keyStr,0,$length);
        return $keys;
    }
    
    // 验证URL的合法性
    function is_url($url){
        $r = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
        if(preg_match($r,$url)){
            return true;
        }else{
            return false;
        }
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>