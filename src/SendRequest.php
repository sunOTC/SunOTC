<?php

namespace SunOtc;

class SendRequest
{
    /**
     * @param $url
     * @param string $method
     * @param null $postFields
     * @param null $header
     * @param null $proxy
     * @param null $proxyPort
     * @return bool|string
     */
    public static function curl(
        $url,
        $method = 'GET',
        $postFields = null,
        $header = null,
        $proxy = null,
        $proxyPort = null
    )
    {
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
        }
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($postFields)) {
                    if (is_array($postFields) || is_object($postFields)) {
                        if (is_object($postFields)) {
                            $postFields = SendRequest::object2array($postFields);
                        }
                        $postBodyString = "";
                        $postMultipart = false;
                        foreach ($postFields as $k => $v) {
                            if ("@" != substr($v, 0, 1)) { //Determine whether it is a file upload
                                $postBodyString .= "$k=" . urlencode($v) . "&";
                            } else { //Use multipart/form-data for file upload, otherwise use www-form-urlencoded
                                $postMultipart = true;
                            }
                        }
                        unset($k, $v);
                        if ($postMultipart) {
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                        } else {
                            curl_setopt(
                                $ch,
                                CURLOPT_POSTFIELDS,
                                substr($postBodyString, 0, -1)
                            );
                        }
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                    }
                }
                break;
            default:
                if (!empty($postFields) && is_array($postFields)) {
                    foreach ($postFields as $key => $value) {
                        $tmp[] = $key . '=' . $value;
                    }
                    $data = implode('&', $tmp);
                    $url .= (strpos($url, '?') === false ? '?' : '&') . $data;
                }
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);

        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        curl_close($ch);

        return $response;
    }


    /**
     * Features：stdClass conversion array
     * @param $object
     * @return mixed
     */
    public static function object2array(&$object)
    {
        $object = json_decode(json_encode($object), true);
        return $object;
    }

}
