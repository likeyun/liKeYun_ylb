<?php
    
    // 编码
    header("Content-type:application/json");
    
    // 获取参数
    $dwz_rkym = $_POST['dwz_rkym'];
    $dwz_zzym = $_POST['dwz_zzym'];
    $dwz_dlym = $_POST['dwz_dlym'];
    $dwz_dlws = $_POST['dwz_dlws'];
    $dwz_type = $_POST['dwz_type'];
    $dwz_urls = $_POST['dwz_urls'];
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 当前登录的用户
        $loginUser = $_SESSION["yinliubao"];
        
        // 随机生成dwz_key（算法1）
        function creatKey($length){
            $keyMember = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
            $keyStr = str_shuffle($keyMember);
            $keys = substr($keyStr,0,$length);
            return $keys;
        }
        
        // 随机生成dwz_key（算法2）
        function creatKeyTwo($length){
            $str = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's', 
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D', 
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', 
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
            $keys = array_rand($str, $length); 
            $keyString = '';
            for($i = 0; $i < $length; $i++){
                $keyString .= $str[$keys[$i]];
            }
            return $keyString;
        }
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
        
        // 过滤参数
        if(empty($dwz_rkym) || !isset($dwz_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名不可为空'
            );
        }else if(empty($dwz_zzym) || !isset($dwz_zzym)){
            
            $result = array(
                'code' => 203,
                'msg' => '中转域名不可为空'
            );
        }else if(empty($dwz_dlym) || !isset($dwz_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名不可为空'
            );
        }else if(empty($dwz_dlws) || !isset($dwz_dlws)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链位数不可为空'
            );
        }else if(empty($dwz_type) || !isset($dwz_type)){
            
            $result = array(
                'code' => 203,
                'msg' => '访问限制不可为空'
            );
        }else if(empty($dwz_urls) || !isset($dwz_urls)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接不可为空'
            );
        }else{
            
            // 规则
            $Guize = '/(http|https):\/\/[^\s]+/';
            preg_match_all($Guize, $dwz_urls, $matches);
            
            // 开始提取链接
            if (!empty($matches[0])) {
                
                // 获取到的链接列表
                $urls = $matches[0];
                
                // 遍历生成
                $createNum = 0; // 次数记录
                $createResultArray = array();
                foreach ($urls as $url) {
                    
                    // 生成dwz_key
                    $dwzKey = creatKey($dwz_dlws);
                    
                    // 生成dwz_id
                    $dwz_id = rand(100000,999999);
                    
                    // 生成标题
                    $dwz_title = '批量生成-'.$dwzKey;
                    
                    // 验证dwz_key是否重复
                    $checkDwzKey = $db->set_table('huoma_dwz')->find(['dwz_key' => $dwzKey]);
                    if($checkDwzKey){
                        
                        // 存在相同的dwz_key
                        // 使用算法2重新生成
                        $dwzKey = creatKeyTwo($dwz_dlws);
                    }
                    
                	// 创建参数
                    $createDwzParams = [
                        'dwz_title'=>$dwz_title,
                        'dwz_rkym'=>$dwz_rkym,
                        'dwz_zzym'=>$dwz_zzym,
                        'dwz_dlym'=>$dwz_dlym,
                        'dwz_type'=>$dwz_type,
                        'dwz_url'=>$url,
                        'dwz_creat_user'=>$loginUser,
                        'dwz_key' => $dwzKey,
                        'dwz_today_pv' => '{"pv":"0","date":"'.date("Y-m-d").'"}',
                        'dwz_id'=>$dwz_id
                    ];
                    
                    // 执行创建
                    $createDwz = $db->set_table('huoma_dwz')->add($createDwzParams);
                    
                    // 创建次数
                    $createNum = $createNum+1;
                    
                    // 执行结果
                    if($createDwz){
                        
                        // 创建结果
                        $createResultArray[] = $dwz_dlym.'/'.$dwzKey;
                        
                    }else{
                        
                        // 创建结果
                        $createResultArray[] = $createDwz;
                    }
                }
                
                // 创建结果
                $result = array(
                    'code' => 200,
                    'msg' => '创建完成！共有'.$createNum.'个链接创建成功！',
                    'createNum' => $createNum,
                    'dwzList' => $createResultArray
                );
                
            } else {
                
                // 无法匹配到链接
                $result = array(
                    'code' => 202,
                    'msg' => '你输入的文案没有包含符合规则的链接！'
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