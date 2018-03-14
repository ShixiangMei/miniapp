<?php
/**
 * Created by PhpStorm.
 * User: mei
 * Date: 2018/3/14
 * Time: 15:20
 */
namespace Msx\MiniApp;

use Illuminate\Session\SessionManager;
use Illuminate\Config\Repository;
use Msx\MiniApp\Helpers\WXBizDataCrypt;
use Msx\MiniApp\Helpers\WechatHelper;



class MiniApp {
    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * MiniApp constructor.
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * User: mei
     * Date: 2018/3/14 15:26
     * @param string $msg
     * @return string
     */
    public function login ($request)
    {

        $data = [];
        $res = WechatHelper::jsCode2Session($request->input('code'));
        if (isset($res->errcode)){
            $data['code'] = 500;
            $data['msg'] = $res->errmsg;

            return json_encode($data);
        }

        // decrypt user info
        $pc = new WXBizDataCrypt(env('MINIAPP_ID'), $res->session_key);
        $errCode = $pc->decryptData(
            $request->input('encryptedInfoData'),
            $request->input('infoIv'), $userData );
        if ($errCode != 0) {
//            return $this->sendError("解码用户数据错误：$errCode", 200);
            $data['code'] = 500;
            $data['msg'] = "解码用户数据错误：$errCode";

            return json_encode($data);
        }
//        $userData = json_decode($userData);

        // decrypt phone number
        $errCode = $pc->decryptData(
            $request->input('encryptedPhoneData'),
            $request->input('phoneIv'), $phoneData );
        if ($errCode != 0) {
            $data['code'] = 500;
            $data['msg'] = "解码手机号码错误：$errCode";

            return json_encode($data);
        }
//        $phoneData = json_decode($phoneData);

        $data['code'] = 200;
        $data['userData'] = $userData;
        $data['phoneData'] = $phoneData;

        return json_encode($data);
    }
}