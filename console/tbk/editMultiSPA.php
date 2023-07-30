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
        $multiSPA_title = trim($_POST['multiSPA_title']);
        $multiSPA_project = trim($_POST['multiSPA_project']);
        $multiSPA_rkym = trim($_POST['multiSPA_rkym']);
        $multiSPA_ldym = trim($_POST['multiSPA_ldym']);
        $multiSPA_dlym = trim($_POST['multiSPA_dlym']);
        $multiSPA_img = trim($_POST['multiSPA_img']);
        $multiSPA_id = trim($_POST['multiSPA_id']);
        
        // 过滤参数
        if(empty($multiSPA_title) || !isset($multiSPA_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($multiSPA_project) || !isset($multiSPA_project)){
            
            $result = array(
                'code' => 203,
                'msg' => '项目内容未填写'
            );
        }else if(empty($multiSPA_rkym) || !isset($multiSPA_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($multiSPA_ldym) || !isset($multiSPA_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($multiSPA_dlym) || !isset($multiSPA_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($multiSPA_id) || !isset($multiSPA_id)){
            
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
            
            // 验证当前要编辑的multiSPA_id的发布者是否为当前登录的用户
            $getMultiSPACreatUserResult = $db->set_table('huoma_tbk_mutiSPA')->find(['multiSPA_id'=>$multiSPA_id]);
            $create_user = json_decode(json_encode($getMultiSPACreatUserResult))->create_user;
            
            // 用户一致：允许操作
            if($create_user == $LoginUser){
        
                // 需更新的字段
                $updateMultiSPAParams = [
                    'multiSPA_title'=>$multiSPA_title,
                    'multiSPA_project'=>$multiSPA_project,
                    'multiSPA_rkym'=>$multiSPA_rkym,
                    'multiSPA_ldym'=>$multiSPA_ldym,
                    'multiSPA_dlym'=>$multiSPA_dlym,
                    'multiSPA_img'=>$multiSPA_img
                ];
                
                // 更新条件
                $updateMultiSPACondition = [
                    'multiSPA_id' => $multiSPA_id,
                    'create_user' => $LoginUser
                ];
                
                // 执行更新
                $updateMultiSPASQL = $db->set_table('huoma_tbk_mutiSPA')->update($updateMultiSPACondition,$updateMultiSPAParams);
                if($updateMultiSPASQL){
                    
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
            'msg' => '未登录'
        );
    }

    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>