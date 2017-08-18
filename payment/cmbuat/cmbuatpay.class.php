<?php
require_once 'cmbUATUtils.php';

require_once(dirname(__FILE__) . '/../../lib/phpseclib/Crypt/RSA.php');
require_once(dirname(__FILE__) . '/../../lib/phpseclib/Math/BigInteger.php');

//$cmbuatpay = new cmbuatpay();
//$order = array(
//    'order_sn' => '1000001001',
//    'order_name' => '测试商品3',
//    'amount' => 100,
//);
////
//$tagCode = $cmbuatpay->get_code($order);
//$tagCode = "ABCKK";
//$tmpfname = tempnam(ROOT_PATH . 'tmp/caches/', uniqid());
//create_qr($tagCode, $tmpfname);
//$imageString = file_get_contents($tmpfname);
//unlink($tmpfname);
//echo $tmpfname;exit;
//echo '<img src="data:image/png;base64,' . base64_encode($imageString) . '">';


class cmbuatpay
{
    //测试地址
    private $host = 'https://sandbox.ccc.cmbchina.com';
    //生产地址,上线时切换到这个地址
    //private $host = 'https://open.cmbchina.com';

    public function __construct()
    {
    }

    /**
     * 生成支付代码
     *
     * @param array $order
     * @return string
     */
    public function get_code($order)
    {
        $cmbUATUtils = new cmbUATUtils();

        //
        // 开始 生成支付协议
        //
        $params = array(
            'mid' => cmbUATUtils::$mid,
            'aid' => cmbUATUtils::$aid,
            'date' => cmbUATUtils::genDate(),
            'random' => cmbUATUtils::genRandom(),
            'billno' => $order['order_sn'],
            'productname' => $order['order_name'],
            'amount' => intval($order['amount']),
            'bonus' => '0',
            'notifyurl' => 'http://localhost/payment/cmbuat/notify_url.php',
        );

        $queryString = $this->mapToQueryString($params, true, false);
        $this->writelog('请求参数: ' . $queryString);

        //生成支付协议签名
        $sign = $cmbUATUtils->sign('cmblife://pay?' . $queryString);

        //生成所有请求内容,urlencode
        $params['sign'] = base64_encode($sign);

        $queryString = $this->mapToQueryString($params, true, true);

        $url1 = 'cmblife://pay?' . $queryString;
        $this->writelog('生成支付协议URL: ' . $url1);

        //
        // 结束 生成支付协议
        //


        //
        // 开始 生成二维码
        //
        //二维码支付需要参数

        $wrapperParams = array(
            'mid' => cmbUATUtils::$mid,
            'aid' => cmbUATUtils::$aid,
            'date' => cmbUATUtils::genDate(),
            'random' => cmbUATUtils::genRandom(),
            'protocol' => $url1,
        );

        $queryString = $this->mapToQueryString($wrapperParams, true, false);
        $this->writelog('二维码所需参数: ' . $queryString);
        //生成校验
        $sign = $cmbUATUtils->sign('releaseTagForQRPay.json?' . $queryString);

        //生成所有请求内容,urlencode
        $wrapperParams['sign'] = base64_encode($sign);

        $queryString = $this->mapToQueryString($wrapperParams, true, true);

        $url2 = $this->host . '/AccessGateway/transIn/releaseTagForQRPay.json?' . $queryString;
        $this->writelog('生成二维码URL: ' . $url2);

        $response = $this->getHttpResponsePOST($url2, $queryString);
        $this->writelog('请求返回结果: ' . $response);

        if ($response == NULL) {
            return '';
        }

        $json = json_decode($response, true);
        if ($json == NULL || $json == FALSE) {
            return '';
        }

        if ($json['respCode'] != '1000') {
            return '';
        }

        return $json['tagCode'];
    }


    /**
     * 生成app支付
     *
     * @param $order
     * @return string
     */
    public function getAppPay($order)
    {
        $cmbUATUtils = new cmbUATUtils();

        //
        // 开始 生成支付协议
        //
        $params = array(
            'mid' => cmbUATUtils::$mid,
            'aid' => cmbUATUtils::$aid,
            'date' => cmbUATUtils::genDate(),
            'random' => cmbUATUtils::genRandom(),
            'billno' => $order['order_sn'],
            'productname' => $order['order_name'],
            'amount' => $order['amount'],
            'bonus' => '0',
            'notifyurl' => 'http://localhost/payment/cmbuat/notify_url.php',
        );

        $queryString = $this->mapToQueryString($params, true, false);

        //生成支付协议签名
        $sign = $cmbUATUtils->sign('cmblife://pay?' . $queryString);

        //生成所有请求内容,urlencode
        $params['sign'] = base64_encode($sign);

        $queryString = $this->mapToQueryString($params, true, true);

        $url1 = 'cmblife://pay?' . $queryString;
        $this->writelog('生成支付协议URL: ' . $url1);

        return $url1;
    }

    /**
     * 参数拼装
     *
     * @param array $map
     * @param $isSort
     * @param $isUrlEncode
     * @return string
     */
    private function mapToQueryString($map = array(), $isSort, $isUrlEncode)
    {
        if ($isSort) {
            ksort($map);
            $tempMap = $map;
        } else {
            $tempMap = $map;
        }

        // map拼接
        $sb = array();
        foreach ($tempMap as $key => $value) {
            $value = trim($value);
            //过滤掉空字符串值的key
            if ($value == '') {
                continue;
            }
            if ($isUrlEncode) {
                $value = urlencode($value);
            }
            $sb[] = $key . '=' . $value;
        }
        return implode('&', $sb);
    }


    private function getHttpResponsePOST($url, $para)
    {
        $fp = fopen(dirname(__FILE__) . '/errorlog.txt', 'w');
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/x-www-form-urlencoded',
        ));

        curl_setopt($curl, CURLOPT_STDERR, $fp);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para);// post传输数据
        $responseText = curl_exec($curl);

        //curl_error($curl);//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        return $responseText;
    }

    private function writelog($word = '')
    {
        $fp = fopen(ROOT_PATH . "payment/cmbuat/log.wf.txt", "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 支付异步回调验签
     *
     * @param array $data
     * @return bool
     */
    public function verifyNotify($data = array())
    {
        //签名
        $sign = $data['sign'];
        $this->writelog("掌上生活返回签名:{$sign}");

        //移除sign
        unset($data['sign']);
        $queryString = $this->mapToQueryString($data, true, false);

        $cmbUATUtils = new cmbUATUtils();
        //base64_decode 后sign长度为256
        $result = $cmbUATUtils->verify($queryString, base64_decode($sign));

        //验证结果
        return $result;
    }
}
