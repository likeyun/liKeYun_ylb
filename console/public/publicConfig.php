<?php

// 程序用途：公共配置
// 最后维护日期：2023-06-03
// 作者：TANKING
// 博客：https://segmentfault.com/u/tanking

// 获取上一级目录
$parentDir = dirname(dirname($_SERVER['PHP_SELF']));

// 获取HTTP协议
$protoCol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// 图片域名（默认获取当前登录管理后台的域名）
// 如需修改请直接将下方【$_SERVER['HTTP_HOST']】替换成你的域名
// 例如替换成：【"www.qq.com"】
$domaiName = $_SERVER['HTTP_HOST'];

// 图片地址路径
// $protoCol是HTTP协议，自动获取当前使用的是HTTP协议还是HTTPS协议
// $domaiName是自动获取当前登录管理后台的域名
// $parentDir是上一级目录名
$imgPathUrl = $protoCol.'://'.$domaiName.$parentDir.'/upload/';
// $imgPathUrl = 'https://ylb.liketube.cn/upload/';

?>