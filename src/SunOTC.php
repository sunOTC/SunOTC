<?php
/**
 * SunOTC.php
 */

namespace SunOTC;

class SunOTC
{

    private $pubKey = null;

    private $priKey = null;

    const HOST = "https://api.sunotc.com";
    /**
     * @var string
     */
    private $app_id;

    /**
     * OtcService constructor.
     * @param string $app_id
     * @param string $priKey
     */
    function __construct($app_id = "", $priKey = "")
    {
        $this->app_id = $app_id;
        $this->priKey = $priKey;
    }


    /**
     * Features：
     * @param $param
     * @return bool|string
     */
    public function subOrderMsg($param)
    {
        $result = $this->getResult("/api/v1/order", $param);
        return $result;
    }

    /**
     * Features：get order msg
     * @param $param
     * @return bool|string
     */
    public function getOrderMsg($param)
    {
        return $this->getResult("/api/v1/order/message", $param);
    }

    /**
     * Features：get trade limit
     * @param $param
     * @return bool|string
     */
    public function getTradeLimit($param)
    {
        return $this->getResult("/api/v1/trade/limit", $param);
    }

    /**
     * Features：get trade price
     * @param $param
     * @return bool|string
     */
    public function getTradePrice($param)
    {
        return $this->getResult("/api/v1/trade/price", $param);
    }

    /**
     * Features：get merc asset
     * @param $param
     * @return bool|string
     */
    public function getMercAsset($param)
    {
        return $this->getResult("/api/v1/merc/assets", $param);
    }


    /**
     * Features：curl request
     * @param $url
     * @param $param
     * @return bool|string
     */
    public function getResult($url, $param)
    {
        $param = json_decode($param, true);
        $param["app_id"] = $this->app_id;
        $param["timestamp"] = time();
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);
        $signer = $this->getSign($param);
        $header = array(
            "Content-Type: application/json; charset=utf-8",
            'Content-Length: ' . strlen($param),
            "sign:$signer"
        );
        return SendRequest::curl(self::HOST . $url, "POST", $param, $header);
    }


    /**
     * Features：get sign
     * @param $param
     * @return bool|string
     */
    public function getSign($param)
    {
        $rsaObject = (new RSA("", $this->priKey));
        return $rsaObject->sign(md5($param));

    }

}