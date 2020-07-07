<?php
/**
 * Rsa.php
 */

namespace SunOtc;

class Rsa
{

    private $pubKey = null;
    private $priKey = null;

    /**
     * error
     * @param $msg
     */
    private function _error($msg)
    {
        die('RSA Error:' . $msg); //TODO
    }

    /**
     * RSA constructor.
     * @param string $public_key
     * @param string $private_key
     */
    public function __construct($public_key = '', $private_key = '')
    {
        if ($public_key) {
            $this->_getPublicKey($public_key);
        }
        if ($private_key) {
            $this->_getPrivateKey($private_key);
        }
    }

    /**
     * create sign
     * @param $data
     * @param string $code
     * @return bool|string
     */
    public function sign($data, $code = 'base64')
    {
        $ret = false;
        if (openssl_sign($data, $ret, $this->priKey,OPENSSL_ALGO_SHA256)) {
            $ret = $this->_encode($ret, $code);
        }
        return $ret;
    }

    /**
     * verify
     * @param $data
     * @param $signature
     * @param string $code
     * @return bool
     */
    public function verify($data, $sign, $code = 'base64')
    {
        $ret = false;
        $sign = $this->_decode($sign, $code);
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $this->pubKey,OPENSSL_ALGO_SHA256)){
                case 1: $ret = true; break;
                case 0:
                case -1:
                default: $ret = false;
            }
        }
        return $ret;
    }

    /**
     * encrypt （base64/hex/bin）
     * @param $data
     * @param string $code
     * @param int $padding
     * @return bool|string
     */
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_ALGO_SHA256)
    {
        $ret = false;
        if (!$this->_checkPadding($padding, 'en')) {
            $this->_error('padding error');
        }
        $RSA_ENCRYPT_BLOCK_SIZE = 117;
        $result                 = '';
        $data                   = str_split($data, $RSA_ENCRYPT_BLOCK_SIZE);
        foreach ($data as $block) {
            openssl_public_encrypt($block, $dataEncrypt, $this->pubKey, $padding);
            $result .= $dataEncrypt;
        }
        if ($result) {
            $ret = $this->_encode($result, $code);
        }

        return $ret;
    }

    /**
     * @param $data
     * @param string (base64/hex/bin）
     * @param int OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block
     * @return bool|string
     */
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_ALGO_SHA256, $rev = false)
    {
        $ret                    = false;
        $data                   = $this->_decode($data, $code);
        $RSA_DECRYPT_BLOCK_SIZE = 128;
        $result                 = '';
        $data                   = str_split($data, $RSA_DECRYPT_BLOCK_SIZE);
        foreach ($data as $block) {
            if (!$this->_checkPadding($padding, 'de')) {
                $this->_error('padding error');
            }
            if ($data !== false) {
                openssl_private_decrypt($block, $dataDecrypt, $this->priKey, $padding);
                $ret    = $rev ? rtrim(strrev($dataDecrypt), "\0") : '' . $dataDecrypt;
                $result .= $ret;
            }
        }
        if ($result) {
            $ret = $result;
        }
        return $ret;
    }

    /**
     * check padding
     * @param $padding
     * @param $type
     * @return bool
     */
    private function _checkPadding($padding, $type)
    {
        if ($type == 'en') {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        } else {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                case OPENSSL_NO_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    /**
     * _encode
     * @param $data
     * @param $code
     * @return string
     */
    private function _encode($data, $code)
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_encode('' . $data);
                break;
            case 'hex':
                $data = bin2hex($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    /**
     * _decode
     * @param $data
     * @param $code
     * @return bool|false|string
     */
    private function _decode($data, $code)
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_decode($data);
                break;
            case 'hex':
                $data = $this->_hex2bin($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    /**
     * get PublicKey
     * @param $key_content
     */
    private function _getPublicKey($key_content)
    {

        $publicKey = str_replace([
            '-----BEGIN PUBLIC KEY-----',
            '-----END PUBLIC KEY-----',
            "\r\n",
            "\n",
        ], ['', '', '', ''], $key_content);
        $string    = trim($publicKey);
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($string, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        //Verify that the public key format is correct
        $isTrue       = openssl_pkey_get_public($publicKey);
        $this->pubKey = !$isTrue ? "false" : $publicKey;
    }

    /**
     * _get PrivateKey
     * @param $key_content
     */
    private function _getPrivateKey($key_content)
    {
        $privKey = str_replace([
            '-----BEGIN RSA PRIVATE KEY-----',
            '-----END RSA PRIVATE KEY-----',
            "\r\n",
            "\n",
        ], ['', '', '', ''], $key_content);
        $string  = trim($privKey);
        $privKey =
            "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($string, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        //Verify that the privite key format is correct
        $isTrue       = openssl_pkey_get_private($privKey);
        $this->priKey = !$isTrue ? "false" : $privKey;
    }

    /**
     * hex to bin
     * @param bool $hex
     * @return bool|false|string
     */
    private function _hex2bin($hex = false)
    {
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }
}
