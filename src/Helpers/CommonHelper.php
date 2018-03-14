<?php
/**
 * Created by PhpStorm.
 * User: wangxiaopei
 * Date: 2/27/18
 * Time: 2:34 PM
 */

namespace Msx\MiniApp\Helpers;
use GuzzleHttp\Client;

class CommonHelper
{
    static public function get($url, $params = null)
    {
        $client = new Client(['timeout' => 10]);
        if ($params)
            $url = $url. "?" . http_build_query($params);
        $res = $client->get($url);

        $text = $res->getBody()->getContents();
        \Log::debug("request: $url");
        \Log::debug("response: ".$text);

        return $text;
    }

    static public function post($url, $params)
    {
        \Log::debug("post: $url");
        \Log::debug($params);

        $client  = new Client(['timeout' => 10]);
        $res = $client->post($url, ['form_params' => $params]);

        $text = $res->getBody()->getContents();

        \Log::debug("response: ".$text);

        return $text;
    }

    static public function postJson($url, $params)
    {
        \Log::debug("post: $url");
        \Log::debug(json_encode($params));

        $client  = new Client(['timeout' => 10, 'Content-Type' => 'application/json']);
        $res = $client->post($url, ['body' => json_encode($params)]);

        $text = $res->getBody()->getContents();

//        \Log::debug("response: ".$text);

        return $text;
    }
}