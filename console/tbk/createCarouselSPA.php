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
        
        // 当前登录的用户
        $loginUser = $_SESSION["yinliubao"];
        
        // 已登录
        $Carousel_title = trim($_POST['Carousel_title']);
        $Carousel_rkym = trim($_POST['Carousel_rkym']);
        $Carousel_ldym = trim($_POST['Carousel_ldym']);
        $Carousel_dlym = trim($_POST['Carousel_dlym']);
        
        // 过滤参数
        if(empty($Carousel_title) || !isset($Carousel_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($Carousel_rkym) || !isset($Carousel_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($Carousel_ldym) || !isset($Carousel_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($Carousel_dlym) || !isset($Carousel_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else{
            
            // ID生成
            $Carousel_id = '10'.mt_rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 随机生成 Carousel_key
            function creatKey($length){
                $keyMember = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }
        
        	// 参数
            $createCarouselSPAParams = [
                'Carousel_id' => $Carousel_id,
                'Carousel_title' => $Carousel_title,
                'Carousel_rkym' => $Carousel_rkym,
                'Carousel_ldym' => $Carousel_ldym,
                'Carousel_dlym' => $Carousel_dlym,
                'Carousel_create_user' => $loginUser,
                'Carousel_key' => creatKey(5)
            ];
            
            // 创建
            $createCarouselSPA = $db->set_table('ylb_CarouselSPA')->add($createCarouselSPAParams);
            
            if($createCarouselSPA){
                
                // 创建成功
                $result = array(
                    'code' => 200,
                    'msg' => '创建成功'
                );
            }else{
                
                // 创建失败
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

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>