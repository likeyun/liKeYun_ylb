<?php

	// alipay当面付支付配置
	// https://open.alipay.com
	// 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
	$appid = 'xxx';

	//付款成功后的异步回调地址
	$notifyUrl = 'xxx';

	// 商户私钥，填写对应签名算法类型的私钥
	// 如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
	$rsaPrivateKey='xxx';
	
?>