<?php
declare (strict_types=1);

namespace app\index\controller;

use think\Request;

class JiamiTest
{
    public function index()
    {
        return app()->getRootPath();
    }

    /**
     * 生成并导出证书
     *
     * @return string
     */
    public function exportSSLFile(): string
    {
        $config = [
            "digest_alg"       => "sha512",
            "private_key_bits" => 4096,           //字节数  512 1024 2048  4096 等
            "private_key_type" => OPENSSL_KEYTYPE_RSA,   //加密类型
        ];
        $res = openssl_pkey_new($config);

        if ($res == false) {
            return '失败';
        } else {
            openssl_pkey_export($res, $private_key);
            $public_key = openssl_pkey_get_details($res)["key"];
            file_put_contents(app()->getRootPath() . "config/cert/cert_public.key", $public_key);
            file_put_contents(app()->getRootPath() . "config/cert/cert_private.pem", $private_key);
            openssl_free_key($res);

            return '成功';
        }
    }

    /**
     * 公钥加密 私钥解密
     *
     * @param Request $request
     *
     * @return string
     */
    public function authCode(Request $request): string
    {
        $ssl_public = file_get_contents(app()->getRootPath() . "config/cert/cert_public.key");
        $ssl_private = file_get_contents(app()->getRootPath() . "config/cert/cert_private.pem");
        $pi_key = openssl_pkey_get_private($ssl_private);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $pu_key = openssl_pkey_get_public($ssl_public);  //这个函数可用来判断公钥是否是可用的
        if (false == ($pi_key || $pu_key)) {
            return '证书错误';
        }

        $string = $request->post('string');
        $operation = $request->post('operation');
        $data = "";
        if ($operation == 'D') {
            openssl_private_decrypt(base64_decode($string), $data, $pi_key);//私钥解密
        } else {
            openssl_public_encrypt($string, $data, $pu_key);//公钥加密
            $data = base64_encode($data);
        }

        return $data;
    }

    //私钥签名
    public function sign($string): string
    {
        $ssl_private = file_get_contents(app()->getRootPath() . "config/cert/cert_private.pem");
        $pi_key = openssl_pkey_get_private($ssl_private);

        if (false == ($pi_key)) {
            return '证书错误';
        }

        openssl_sign($string, $signature, $pi_key);//生成签名
        $data = base64_encode($signature);
        openssl_free_key($pi_key);

        return $data;
    }

    //公钥验签 1正确 0错误 -1内部错误
    public function verifySign($string, $signData)
    {
        $ssl_public = file_get_contents(app()->getRootPath() . "config/cert/cert_public.key");
        $pu_key = openssl_pkey_get_public($ssl_public);

        if (false == ($pu_key)) {
            return '证书错误';
        }

        $verify = openssl_verify($string, base64_decode($signData), $pu_key);
        openssl_free_key($pu_key);

        return $verify;
    }
}
