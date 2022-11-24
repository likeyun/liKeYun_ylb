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
        // 接收参数
        $dwz_title = trim($_POST['dwz_title']);
        $dwz_rkym = trim($_POST['dwz_rkym']);
        $dwz_zzym = trim($_POST['dwz_zzym']);
        $dwz_dlym = trim($_POST['dwz_dlym']);
        $dwz_dlws = trim($_POST['dwz_dlws']);
        $dwz_type = trim($_POST['dwz_type']);
        $dwz_url = trim($_POST['dwz_url']);
        $dwz_creat_user = trim($_SESSION["yinliubao"]);
        
        // 验证URL合法性
        function is_url($url){
            $r = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
            if(preg_match($r,$url)){
                
                return TRUE;
            }else{
                
                return FALSE;
            }
        }
        
        // 过滤参数
        if(empty($dwz_title) || $dwz_title == '' || $dwz_title == null || !isset($dwz_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未设置'
            );
        }else if(empty($dwz_rkym) || $dwz_rkym == '' || $dwz_rkym == null || !isset($dwz_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($dwz_zzym) || $dwz_zzym == '' || $dwz_zzym == null || !isset($dwz_zzym)){
            
            $result = array(
                'code' => 203,
                'msg' => '中转域名未选择'
            );
        }else if(empty($dwz_dlym) || $dwz_dlym == '' || $dwz_dlym == null || !isset($dwz_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($dwz_dlws) || $dwz_dlws == '' || $dwz_dlws == null || !isset($dwz_dlws)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链位数未选择'
            );
        }else if(empty($dwz_type) || $dwz_type == '' || $dwz_type == null || !isset($dwz_type)){
            
            $result = array(
                'code' => 203,
                'msg' => '访问限制未选择'
            );
        }else if(empty($dwz_url) || $dwz_url == '' || $dwz_url == null || !isset($dwz_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接未填写'
            );
        }else if(is_url($dwz_url) === FALSE){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接不是正确的URL格式'
            );
        }else{
            
            // ID生成
            $dwz_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 随机生成dwz_key
            function creatKey($length){
                $keyMember = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }
        
        	// 参数
            $creatdwz = [
                'dwz_title'=>$dwz_title,
                'dwz_rkym'=>$dwz_rkym,
                'dwz_zzym'=>$dwz_zzym,
                'dwz_dlym'=>$dwz_dlym,
                'dwz_type'=>$dwz_type,
                'dwz_url'=>$dwz_url,
                'dwz_creat_user'=>$dwz_creat_user,
                'dwz_key' => creatKey($dwz_dlws),
                'dwz_id'=>$dwz_id
            ];
            
            // 执行SQL
            $creatdwzResult = $db->set_table('huoma_dwz')->add($creatdwz);
            
            // 判断执行结果
            if($creatdwzResult){
                
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
            'msg' => '未登录或登录过期'
        );
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>