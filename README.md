# cmbuatpay
招行掌上生活支付
# 说明
使用第三方库类库phpseclib,用于兼容php5.2的版本,以及一些php加密函数兼容版本问题
仅提供样例,支付协议请求,验证支付回调,都测过没有问题
开发时申请创建一个测试账号 https://open.cmbchina.com/Platform/
里面提供一些加密验签的功能

## 生成公私钥
https://open.cmbchina.com/Platform/#/test/other/genKey
选择生成类型及长度,更新到cmbUATUtils.php中
私钥类型
RSA
私钥长度
2048

## 其他
接口权限
    如果报通过该 [AC1003] aid,interfaceName 查不到对应关系，需要在招行配置相关权限
支付回调域名
    配置支付回调域名，域名后的路径不需要指定，只需要设置域名即可
公私钥
    将生成的公私钥发给招行，招行配置后可以使用
关于支付成功后没有回调请求,联系掌上开发人员配置回调的问题,大多是对外开放的权限没有开