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
 *      //       \
 *     //|   .   |\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))    高山仰止,景行行止.虽不能至,心向往之.
 *
 */

namespace app\index\controller;

use think\Controller;

use think\Db;

class Pay extends Controller
{

    /**
     * session检测
     * @Author: 闻子 < QQ：270988107 >
     */
    public function getSession()
    {
        $AdminExistence = session('admin.id');
        if (!$AdminExistence) {
            return $this->error('登录超时，请重新登录','index/member/login');
        }
    }

    /**
     * 发起支付
     * @Author: 闻子 < QQ：270988107 >
     */
    public function doalipay()
    {
        $this->getSession();
        $alipay_config['partner'] = config('alipay.app_id');
        $alipay_config['seller_id'] = $alipay_config['partner'];
        $alipay_config['key'] = config('alipay.key');
        $alipay_config['notify_url'] = config('alipay.notify_url');
        $alipay_config['return_url'] = config('alipay.return_url');
        $alipay_config['sign_type'] = strtoupper(trim('MD5'));
        $alipay_config['input_charset'] = strtolower('utf-8');
        $alipay_config['cacert'] = getcwd() . '\\cacert.pem';
        $alipay_config['transport'] = 'http';
        $alipay_config['payment_type'] = "1";
        $alipay_config['anti_phishing_key'] = "";
        $alipay_config['exter_invoke_ip'] = "";
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = input('post.out_trade_no');
        //订单名称，必填
        $subject = input('post.subject');
        //付款金额，必填
        $total_fee = input('post.total_fee');
        //商品描述，可空
        $body = input('post.body');
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($alipay_config['partner']),
            "seller_id" => $alipay_config['seller_id'],
            "payment_type" => $alipay_config['payment_type'],
            "notify_url" => $alipay_config['notify_url'],
            "return_url" => $alipay_config['return_url'],
            "anti_phishing_key" => $alipay_config['anti_phishing_key'],
            "exter_invoke_ip" => $alipay_config['exter_invoke_ip'],
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))

        );

        $alipaySubmit = new \apppay\AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        echo $html_text;
    }

    /**
     * 支付宝回调
     * @Author: 闻子 < QQ：270988107 >
     */
    public function return_url()
    {
        $this->getSession();
        $alipay_config['partner'] = config('alipay.app_id');
        $alipay_config['seller_id'] = $alipay_config['partner'];
        $alipay_config['key'] = config('alipay.key');
        $alipay_config['notify_url'] = config('alipay.notify_url');
        $alipay_config['return_url'] = config('alipay.return_url');
        $alipay_config['sign_type'] = strtoupper(trim('MD5'));
        $alipay_config['input_charset'] = strtolower('utf-8');
        $alipay_config['cacert'] = getcwd() . '\\cacert.pem';
        $alipay_config['transport'] = 'http';
        $alipay_config['payment_type'] = "1";
        $alipay_config['service'] = "create_direct_pay_by_user";
        $alipayNotify = new \apppay\AlipayNotify($alipay_config);

        $verify_result = $alipayNotify->verifyReturn();
        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no = $_GET['out_trade_no'];      //商户订单号
            $trade_no = $_GET['trade_no'];          //支付宝交易号
            $trade_status = $_GET['trade_status'];      //交易状态
            $total_fee = $_GET['total_fee'];         //交易金额
            $notify_id = $_GET['notify_id'];         //通知校验ID。
            $notify_time = $_GET['notify_time'];       //通知的发送时间。
            $buyer_email = $_GET['buyer_email'];       //买家支付宝帐号；
            $body = $_GET['body'];          //商品描述

            $parameter = array(
                "out_trade_no" => $out_trade_no,      //商户订单编号；
                "trade_no" => $trade_no,          //支付宝交易号；
                "total_fee" => $total_fee,         //交易金额；
                "trade_status" => $trade_status,      //交易状态
                "notify_id" => $notify_id,         //通知校验ID。
                "notify_time" => $notify_time,       //通知的发送时间。
                "buyer_email" => $buyer_email,       //买家支付宝帐号
            );


            if ($_GET['trade_status'] == 'TRADE_SUCCESS') {

                // body如为1充值积分
            	if ($body == 1) {
            		Db::name('user')->where('id',session('admin.id'))->setInc('integral', $total_fee);
                    Db::name('pay')->insert(['uid' => session('admin.id'), 'user' => session('admin.tel'), 'jine' => $total_fee, 'type' => 1,'time' => date('Y-m-d H:i:s')]);
                    $this->redirect('/member/pay_sub', ['type' => 'Success']);
            	}
                // body如为2充值金额
                if ($body == 2) {
                    Db::name('user')->where('id',session('admin.id'))->setInc('money', $total_fee);
                    Db::name('pay')->insert(['uid' => session('admin.id'), 'user' => session('admin.tel'), 'jine' => $total_fee, 'type' => 1,'time' => date('Y-m-d H:i:s')]);
                    $this->redirect('/member/pay_sub', ['type' => 'Success']);
                }

            } else {
                $this->redirect('/member/pay_integral');

            }


        } else {
            $this->redirect('/member/pay_sub', ['type' => 'Error']);

        }
    }
}