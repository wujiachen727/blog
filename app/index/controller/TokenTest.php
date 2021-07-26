<?php
declare (strict_types=1);

namespace app\index\controller;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use think\Request;
use think\response\Json;

/**
 * 集成JWT-实现token用户身份验证机制
 *
 * Class TokenTest
 *
 * @package app\index\controller
 */
class TokenTest
{
    /**
     * 生成token
     *
     * @param string $userId
     *
     * @return string
     */
    public function createJwt($userId = 'wujiachen'): string
    {
        $key = md5('wujiachen260727#');       //jwt的签发密钥，验证token的时候需要用到
        $time = time();                       //签发时间
        $expire = $time + 14400;              //过期时间
        $token = [
            "user_id" => $userId,
            "iss"     => "http://www.wujiachen.com/",//签发组织
            "aud"     => "wujiachen", //签发作者
            "iat"     => $time,
            "nbf"     => $time,
            "exp"     => $expire
        ];

        return JWT::encode($token, $key);
    }

    /**
     * token校验
     *
     * @param Request $request
     *
     * @return array|Json
     */
    public function verifyJwt(Request $request)
    {
        $jwt = $request->post('jwt');
        $key = md5('wujiachen260727#');
        try {
            $jwtAuth = json_encode(JWT::decode($jwt, $key, ['HS256']));
            $authInfo = json_decode($jwtAuth, true);

            $msg = [];
            if (!empty($authInfo['user_id'])) {
                $msg = ['status' => 1001, 'msg' => 'Token验证通过'];
            } else {
                $msg = ['status' => 1002, 'msg' => 'Token验证不通过,用户不存在'];
            }

            return json($msg);
        } catch (ExpiredException $e) {
            return json(['status' => 1003, 'msg' => 'Token过期']);
        } catch (\Exception $e) {
            return json(['status' => 1002, 'msg' => 'Token无效']);
        }
    }
}
