<?php
/**
 * 工具类型
 * 生成加密,按照__construct 里的,一般不用修改
 * 使用phpsecilib就是为了兼容php5.2及其他版本的函数不支持的问题
 *
 * Class cmbUATUtils
 */
class cmbUATUtils
{

    public static $mid = 'c71587ef69eb327caa0c338813032f60';
    public static $aid = '07d133256948387587ffea7ffcb06d4b';


    //生产公钥
    protected $MERCHANT_PUB_KEY = '-----BEGIN PUBLIC KEY-----
YOUR PUBLIC KEY HERE
-----END PUBLIC KEY-----';
    //生产私钥
    protected $MERCHANT_PRI_KEY = '-----BEGIN RSA PRIVATE KEY-----
YOUR PRIVATE KEY HERE
-----END RSA PRIVATE KEY-----';

    //生产验证公钥
    protected $VERIFY_PUB_KEY = '-----BEGIN PUBLIC KEY-----
CMBUAT VERIFY PUBLIC KEY HERE
-----END PUBLIC KEY-----';

    protected $rsa;

    /**
     * X constructor.
     */
    public function __construct()
    {
        $this->rsa = new Crypt_RSA();

        $this->rsa->setHash('sha256');
        //设置签名加密方式
        $this->rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        //设置加密方式
        $this->rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
    }

    /**
     * 生成签名
     *
     * @param $plaintext
     * @return string
     */
    public function sign($plaintext)
    {
        $this->rsa->loadKey($this->MERCHANT_PRI_KEY);
        $signature = $this->rsa->sign($plaintext);
        return $signature;
    }

    /**
     * 验签,使用商户的公钥对返回的参数验签
     *
     * @param $plaintext
     * @return string
     */
    public function checkSign($plaintext)
    {
        $this->rsa->loadKey($this->VERIFY_PUB_KEY);
        $signature = $this->rsa->sign($plaintext);
        return $signature;
    }

    /**
     * 验签,使用招行的公钥对返回的参数验签
     *
     * @param string $plaintext queryString 不包含 sign
     * @param mixed $signature base64_decode 后的签名
     * @return bool
     */
    public function verify($plaintext, $signature)
    {
        //验签公钥
        $this->rsa->loadKey($this->VERIFY_PUB_KEY);
        //校验
        $signature = $this->rsa->verify($plaintext, $signature);
        //返回结果
        return $signature;
    }

    /**
     * 生成日期，格式为yyyyMMddHHmmss
     *
     * @return string
     */
    public static function genDate()
    {
        return date('YmdHis');
    }


    /**
     * 生成随机数
     *
     * @return mixed
     */
    public static function genRandom()
    {
        return str_replace('-', '', uniqid());
    }
}