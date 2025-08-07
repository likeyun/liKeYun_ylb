<?php

    // 编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if (isset($_SESSION["yinliubao"])) {
    
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
    
        // 获取卡密文件
        $kmFile = $_POST['kmFile'];
    
        // 获取绑定的卡密项目ID
        $kami_id = $_POST['kami_id'];
    
        if ($kmFile) {
            
            if(!$kami_id) {
                
                $result = array(
                    'code' => 202,
                    'msg' => '非法请求！'
                );
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                exit;
            }
    
            // 检查文件是否存在
            if (file_exists($kmFile)) {
                
                // 数据库配置
                include '../Db.php';

                // 实例化类
                $db = new DB_API($config);
                
                // 验证当前登录的用户是不是管理员
                $checkUser = $db->set_table('huoma_user')->find(['user_name' => $LoginUser]);
                $user_admin = $checkUser['user_admin'];
            
                if($user_admin == 1) {
                    
                    // 管理员
                    // 用于存储CSV数据的数组
                    $jsonData = array();
                    
                    // 读取上传的文件
                    if (($handle = fopen($kmFile, "r")) !== FALSE) {
                        
                        // 忽略第一行
                        fgetcsv($handle, 1000, ",");
                        
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            
                            // 手动转换编码
                            $data = array_map(function($value) {
                                return mb_convert_encoding($value, 'UTF-8', 'CP936');
                            }, $data);
                            
                            // 将数据添加为一个关联数组
                            $rowData = array();
                            for ($i = 0; $i < count($data); $i++) {
                                $rowData["column_$i"] = $data[$i];
                            }
                            
                            // 将关联数组添加到数组中
                            $jsonData[] = $rowData;
                        }
                        fclose($handle);
                    }
                    
                    // 如果有数据
                    if($jsonData) {
                        
                        $addSuccess = 0; // 定义一个变量记录添加成功的个数
                        $addError = 0; // 定义一个变量记录添加失败的个数
                        $addRepeat = 0; // 定义一个变量记录添加重复的个数
                        
                        // 逐一循环
                        foreach ($jsonData as $kmObject) {
                            
                            $km = $kmObject['column_0']; // 卡密
                            $km_expiryDate = $kmObject['column_1']; // 有效期
                            $km_expireDate = $kmObject['column_2']; // 到期时间
                            $km_beizhu = $kmObject['column_3']; // 备注

                            // 去重
                            // 以相同卡密为重复导入的标记
                            $checkExist = $db->set_table('ylb_kmlist')->find(['km' => $km]);
                            
                            if($checkExist) {
                                
                                // 如果已存在
                                // 记录重复
                                $addRepeat++;
                                $addError++;
                            }else {
                                
                                // 不存在
                                // 插入数据库
                                // 生成唯一id
                                $km_id = rand(100000,999999);
                                
                                // 插入数据库
                                $addKmData = [
                                    'km' => $km,
                                    'km_id' => $km_id,
                                    'km_expiryDate' => $km_expiryDate,
                                    'km_expireDate' => $km_expireDate,
                                    'km_beizhu' => $km_beizhu,
                                    'km_addUser' => $LoginUser,
                                    'kami_id' => $kami_id
                                ];
                
                                // 执行SQL
                                $addKm = $db->set_table('ylb_kmlist')->add($addKmData);
                                
                                if($addKm) {
                                    
                                    // 添加成功
                                    $addSuccess++;
                                    
                                }else {
                                    
                                    // 添加失败
                                    $addError++;
                                }
                            }
                        }
                        
                        sleep(1);
                        
                        // 导入完成
                        $result = array(
                            'code' => 200,
                            'msg' => '导入完成！',
                            'addSuccess' => $addSuccess,
                            'addError' => $addError,
                            'addRepeat' => $addRepeat,
                        );
                    }
                    
                    // 导入完成
                    // 删除临时文件
                    unlink($kmFile);
                    
                    // 获取km_total
                    $km_total = $db->set_table('ylb_kmlist')->getCount(['kami_id' => $kami_id]);
                    
                    // 更新km_total
                    $db->set_table('ylb_kami')->update(['kami_id' => $kami_id], ['km_total' => $km_total]);
                }else {
                    
                    // 非管理员
                    $result = array(
                        'code' => 201,
                        'msg' => '没有导入权限~',
                    );
                }
            } else {
    
                // 文件不存在
                $result = array(
                    'code' => 202,
                    'msg' => '卡密文件不存在！'
                );
            }
        } else {
    
            // 卡密文件获取失败
            $result = array(
                'code' => 202,
                'msg' => '请上传卡密文件！'
            );
        }
    } else {
    
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>
