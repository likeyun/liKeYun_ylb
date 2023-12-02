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
        $zjy_long_title = trim($_POST['zjy_long_title']);
        $zjy_short_title = trim($_POST['zjy_short_title']);
        $zjy_tkl = trim($_POST['zjy_tkl']);
        $zjy_original_cost = trim($_POST['zjy_original_cost']);
        $zjy_discounted_price = trim($_POST['zjy_discounted_price']);
        $zjy_rkym = trim($_POST['zjy_rkym']);
        $zjy_ldym = trim($_POST['zjy_ldym']);
        $zjy_dlym = trim($_POST['zjy_dlym']);
        $zjy_goods_img = trim($_POST['zjy_goods_img']);
        // $zjy_goods_link = trim($_POST['zjy_goods_link']);
        $zjy_goods_link = ''; // // 功能不稳定，下线，用空白内容代替
        $zjy_create_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($zjy_long_title) || !isset($zjy_long_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '长标题未填写'
            );
        }else if(empty($zjy_short_title) || !isset($zjy_short_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '短标题未填写'
            );
        }else if(empty($zjy_tkl) || !isset($zjy_tkl)){
            
            $result = array(
                'code' => 203,
                'msg' => '淘口令未填写'
            );
        }else if(empty($zjy_original_cost) || !isset($zjy_original_cost)){
            
            $result = array(
                'code' => 203,
                'msg' => '原价未填写'
            );
        }else if(empty($zjy_discounted_price) || !isset($zjy_discounted_price)){
            
            $result = array(
                'code' => 203,
                'msg' => '券后价未填写'
            );
        }else if(empty($zjy_rkym) || !isset($zjy_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($zjy_ldym) || !isset($zjy_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($zjy_dlym) || !isset($zjy_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($zjy_goods_img) || !isset($zjy_goods_img)){
            
            $result = array(
                'code' => 203,
                'msg' => '商品主图未上传'
            );
        }else{
            
            // ID生成
            $zjy_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 随机生成zjy_key
            function creatKey($length){
                $keyMember = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }
        
        	// 参数
            $creatZjy = [
                'zjy_id'=>$zjy_id,
                'zjy_long_title'=>$zjy_long_title,
                'zjy_short_title'=>$zjy_short_title,
                'zjy_tkl'=>$zjy_tkl,
                'zjy_original_cost'=>$zjy_original_cost,
                'zjy_discounted_price'=>$zjy_discounted_price,
                'zjy_rkym'=>$zjy_rkym,
                'zjy_ldym'=>$zjy_ldym,
                'zjy_dlym'=>$zjy_dlym,
                'zjy_goods_img'=>$zjy_goods_img,
                'zjy_goods_link'=>$zjy_goods_link,
                'zjy_create_user'=>$zjy_create_user,
                'zjy_key' => creatKey(4)
            ];
            
            // 执行SQL
            $creatZjyResult = $db->set_table('huoma_tbk')->add($creatZjy);
            
            // 判断执行结果
            if($creatZjyResult){
                
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