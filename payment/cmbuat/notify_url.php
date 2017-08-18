<?php
/**
 * 支付回调,主要是验签操作,对对调的内容进行验签.
 * 开发期可以手动设置验签通过
 * 最大支付额度为 50000, 超过50000需要分多次支付
 */

require_once(dirname(__FILE__) . '/cmbuatpay.class.php');
function logResult($word = '')
{
    global $curr_path;
    $fp = fopen("{$curr_path}/cmbuat_pay_log.txt", "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}
//$_POST = array (  'cardId' => '3iolmjjumth8ynwljzzp41ozhsjwtl5y',  'result' => '2',  'aid' => '07d133256948387587ffea7ffcb06d4b',  'date' => '20170801154619',  'sign' => 'mLUiaGat81kgn1Bqhs29XmFWiIG8oEwrIOp8ddvI6aOPfUo28l2rwRj992lan9Jt4cDMr7J+ATZbNojppCeP1G23qP+cREVwDRNzaeCCYEEa/E1pioAKmkiUqfKHtg5LY/Ef2Gq5Dv59EHNkYU82F7A17oywJ6vVwYRCebFqqNoWT4nmbdWJwC7JEjwL0EBMCvxaFDqVXgXalhkKw4cdtvYqfLF0OjQ34VTmg7bZ06ODYgmmCQchRU+Q2Au6ypZ0kR8TuY7JsOv9TSPO9Jw4kB0QRGtTa/4bgh69yQRkoxw/pXsruf+C/sgkPPKUkU2wAyRF5tQelRnCZ13Bbo0PgA==',  'amount' => '10',  'message' => '支付成功',  'shieldcardno' => '4392********0507',  'paytype' => '1000',  'refnum' => '721344690819',  'bonus' => '0',  'mid' => 'c71587ef69eb327caa0c338813032f60',  'bankpayserial' => '9f273fe85a2d44ef841f385c635c008a',  'billno' => '2017080153084133106606',  'notifyUrl' => 'http://webshop.shankaisports.com/payment/cmbuat/notify_url.php',  'discountamount' => '0',);
logResult("招行异步回调数据: " . str_replace("\n", "", var_export($_REQUEST, true)));
logResult("招行异步回调数据: " . str_replace("\n", "", var_export($_POST, true)));

$data = !empty($_POST) ? $_POST : '';
if (empty($data)) {
    header("Content-type: application/json; charset=utf-8");
    logResult("无法获取返回数据");
    echo '{"respCode":"1001"}';
    exit;
}

$return_data = json_encode($data);

$trade_no = $data['billno'];//交易单号
$bankpayserial = $data['bankpayserial'];//支付流水号
$refnum = $data['refnum'];//交易参考号
$amount = $data['amount'];//订单金额

//校验
$cmbuatPay = new cmbuatpay();
$verify_result = $cmbuatPay->verifyNotify($data);
logResult("签名校验结果:" . ($verify_result ? "成功" : "失败"));

////临时设置为成功
//$verify_result = 1;

if ($verify_result) {//验证成功
    logResult("[支付订单]订单付款成功,此订单号是:" . $trade_no);

    header("Content-type: application/json; charset=utf-8");
    echo '{"respCode":"1000"}';
    exit;
} else {
    //验证失败
    logResult("验证失败");

    header("Content-type: application/json; charset=utf-8");
    echo '{"respCode":"1001"}';
    //调试用，写文本函数记录程序运行情况是否正常
    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    exit;
}
