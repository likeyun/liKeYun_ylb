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
        $zjy_long_title = trim($_POST['zjy_long_title']);
        $zjy_short_title = trim($_POST['zjy_short_title']);
        $zjy_tkl = trim($_POST['zjy_tkl']);
        $zjy_original_cost = trim($_POST['zjy_original_cost']);
        $zjy_discounted_price = trim($_POST['zjy_discounted_price']);
        $zjy_rkym = trim($_POST['zjy_rkym']);
        $zjy_ldym = trim($_POST['zjy_ldym']);
        $zjy_dlym = trim($_POST['zjy_dlym']);
        $zjy_goods_img = trim($_POST['zjy_goods_img']);
        $zjy_id = trim($_POST['zjy_id']);
        
        // 过滤参数
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
        }else if(empty($zjy_id) || !isset($zjy_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 验证当前要编辑的zjy_id的发布者是否为当前登录的用户
            $getZjyCreatUserResult = $db->set_table('huoma_tbk')->find(['zjy_id'=>$zjy_id]);
            $zjy_create_user = json_decode(json_encode($getZjyCreatUserResult))->zjy_create_user;
            
            // 用户一致：允许操作
            if($zjy_create_user == $LoginUser){
                
                // 需更新的字段
                $updateZjyData = [
                    'zjy_long_title'=>$zjy_long_title,
                    'zjy_short_title'=>$zjy_short_title,
                    'zjy_tkl'=>$zjy_tkl,
                    'zjy_original_cost'=>$zjy_original_cost,
                    'zjy_discounted_price'=>$zjy_discounted_price,
                    'zjy_rkym'=>$zjy_rkym,
                    'zjy_ldym'=>$zjy_ldym,
                    'zjy_dlym'=>$zjy_dlym,
                    'zjy_goods_img'=>$zjy_goods_img
                ];
                
                // 更新条件
                $updateZjyDataCondition = [
                    'zjy_id' => $zjy_id,
                    'zjy_create_user' => $LoginUser
                ];
                
                // 执行更新update(条件，字段)
                $updateZjyResult = $db->set_table('huoma_tbk')->update($updateZjyDataCondition,$updateZjyData);
                if($updateZjyResult){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '更新成功'
                    );
                }else{
                    
                    // 更新失败
                    $result = array(
                        'code' => 202,
                        'msg' => '更新失败'
                    );
                }
                
            }else{
                
                // 用户不一致
                $result = array(
                    'code' => 202,
                    'msg' => '非法操作'
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