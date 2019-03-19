<?php
/**
 * @Statement: [ An Ordinary Person ]
 * @Author: 闻子 < QQ：270988107 >
 * @Copyright: 闻子 2016-2019 All rights reserved.
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))    高山仰止,景行行止.虽不能至,心向往之.
 *
 */

/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * return 签名结果
*/
function rsaSign($prestr) {
    $config=config('alipay.');
    $private_key=file_get_contents($config['private_key_path']);
    $pkeyid=openssl_get_privatekey($private_key);
    openssl_sign($prestr, $sign, $pkeyid);
    openssl_free_key($pkeyid);
    $sign=base64_encode($sign);
    return $sign;
}

/**
 * 验证签名
 * @param $prestr 需要签名的字符串
 * @param $sign 签名结果
 * return 签名结果
*/
function rsaVerify($prestr, $sign) {
    $sign=base64_decode($sign);
    $config=config('alipay.');
    $public_key=file_get_contents($config['public_key_path']);
    $pkeyid=openssl_get_publickey($public_key);
    if ($pkeyid) {
        $verify=openssl_verify($prestr, $sign, $pkeyid);
        openssl_free_key($pkeyid);
    }
    if($verify == 1){
        return true;
    }else{
        return false;
    }
 }