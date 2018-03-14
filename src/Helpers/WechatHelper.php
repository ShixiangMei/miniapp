<?php
/**
 * Created by PhpStorm.
 * User: wangxiaopei
 * Date: 2/23/18
 * Time: 5:25 PM
 */

namespace Msx\MiniApp\Helpers;
//use App\Helpers\CommonHelper;
use GuzzleHttp\Client;

class WechatHelper
{
    static public function jsCode2Session($code)
    {
        $params = [
            'appid' => env('MINIAPP_ID'),
            'secret' => env('MINIAPP_SECRET'),
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];

        $text =  CommonHelper::get("https://api.weixin.qq.com/sns/jscode2session", $params);
        return json_decode($text);
    }

    static public function getMiniAppQrCode($page, $scene)
    {
        // check code exists
        $filename = substr(md5($page.$scene), 10) . '.jpg';
        $path = "public/referrer-code/".$filename;
        if (!file_exists('public/referrer-code'))mkdir('public/referrer-code', 0755);
        if (file_exists($path)) {
            \Log::debug("get code from $path");
            return "/" . $path;
        }

        // fetch code
        $client  = new Client(['timeout' => 10, 'Content-Type' => 'application/json']);
        $res = $client->post("https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . self::getAccessToken(),
            [
                'body' => json_encode([
                    'scene' => $scene,
                    'page' => $page
                ])
            ]);

        \Log::debug('content type response: '.$res->getHeader('Content-Type')[0]);
        // clear the token and refetch the code
        if (substr($res->getHeader('Content-Type')[0],0,16) == 'application/json'){
            self::clearTokenCache();
            return self::getMiniAppQrCode($page, $scene);
        }
        if ($res->getHeader('Content-Type')[0] == 'image/jpeg') {
            file_put_contents($path, $res->getBody()->getContents());
            return "/" . $path;
        }else{
            throw new \Exception("invalid content type of getwxacodeunlimit");
        }
    }

    static public function getPaymentQrCode($string)
    {
        return "http://qr.liantu.com/api.php?text=${string}";
    }

    static public function getAccessToken()
    {
        if (($token = \Cache::get('access_token', null)) &&
            ($expires = \Cache::get('access_expires_in', null)) &&
            ($access_at = \Cache::get('access_at', null))
        ){
            \Log::debug("token/expires/access_at get from \Cache");
            \Log::debug("token: $token, expires: $expires, access_at: $access_at");
            // 没有过期
            if (time() < ($expires + $access_at)){
                \Log::debug("not expired");
                return $token;
            }
        }

        $res = CommonHelper::get("https://api.weixin.qq.com/cgi-bin/token", [
            'appid' => env('MINIAPP_ID'),
            'secret' => env('MINIAPP_SECRET'),
            'grant_type' => 'client_credential'
        ]);

        $res = json_decode($res);

        if (isset($res->errcode)){
            throw new \Exception($res->errmsg);
        }

        \Cache::put('access_token', $res->access_token, 60 * 24);
        \Cache::put('access_expires_in', $res->expires_in, 60 * 24);
        \Cache::put('access_at', time(), 60 * 24);

        return $res->access_token;
    }

    static public function clearTokenCache()
    {
        \Cache::forget('access_token');
        \Cache::forget('access_expires_in');
        \Cache::forget('access_at');
    }


    static public function paymentClient()
    {
        return \EasyWeChat\Factory::payment([
            'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),  // 微信支付APPID
            'mch_id' => env('WECHAT_PAY_MERCHANT_ID'),  // 微信支付MCHID 商户收款账号
            'key' => env('WECHAT_PAY_KEY'),  // 微信支付KEY
            'notify_url' => route('payment.wepay_callback'), // 接收支付状态的连接
        ]);
    }

    static public function unifyOrder($tx)
    {
        $app = self::paymentClient();
        return $app->order->unify([
            'product_id' => $tx->type,
            'body' => $tx->typeLabel(),
            'out_trade_no' => $tx->tx_no,
            'total_fee' => $tx->amount * 100,
            'trade_type' => 'NATIVE',
        ]);
    }
}