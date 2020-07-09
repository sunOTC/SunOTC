<?php
/**
 * OtcService.php
 */

namespace SunOtc;

class OtcService
{

    private $pubKey = null;

    private $priKey = null;

    const HOST = "https://api.sunotc.com";

    /**
     * OtcService constructor.
     * @param string $pubKey
     * @param string $priKey
     */
    function __construct($pubKey = "", $priKey = "")
    {
        $this->pubKey = $pubKey;
        $this->priKey = $priKey;
    }


    /**
     * Features：
     * @param $param
     * @return bool|string
     */
    public function subOrderMsg($param)
    {
        $result = $this->getResut("/api/v1/order", $param);
        return $result;
    }

    /**
     * Features：get order msg
     * @param $param
     * @return bool|string
     */
    public function getOrderMsg($param)
    {
        return $this->getResut("/api/v1/order/message", $param);
    }

    /**
     * Features：get trade limit
     * @param $param
     * @return bool|string
     */
    public function getTradeLimit($param)
    {
        return $this->getResut("/api/v1/trade/limit", $param);
    }

    /**
     * Features：get trade price
     * @param $param
     * @return bool|string
     */
    public function getTradePrice($param)
    {
        return $this->getResut("/api/v1/trade/price", $param);
    }

    /**
     * Features：get merc asset
     * @param $param
     * @return bool|string
     */
    public function getMercAsset($param)
    {
        return $this->getResut("/api/v1/merc/assets", $param);
    }


    /**
     * Features：curl request
     * @param $url
     * @param $param
     * @return bool|string
     */
    public function getResut($url, $param)
    {
        $param = json_decode($param, true);
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
        $rsaObject = (new Rsa("", $this->priKey));
        return $rsaObject->sign(md5($param));

    }

}