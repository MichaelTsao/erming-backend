<?php
/**
 * Created by PhpStorm.
 * User: caoxiang
 * Date: 16/3/17
 * Time: 下午2:32
 */

namespace app\models;

use yii\httpclient\Client;
use Yii;

class Sms
{
    const SEND_CODE = '获取验证码';
    const NEW_ORDER = '新订单通知';
    const NEW_APPOINT = '新预约通知';

    public static function send($phone, $template)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl('https://api.leancloud.cn/1.1/requestSmsCode')
            ->setFormat(Client::FORMAT_JSON)
            ->setData(['mobilePhoneNumber' => $phone, 'template' => $template])
            ->addHeaders([
                'X-LC-Id' => Yii::$app->params['lean_cloud_id'],
                'X-LC-Key' => Yii::$app->params['lean_cloud_key'],
            ])
            ->send();
        return $response->isOk;
    }

    public static function verify($phone, $code)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl("https://api.leancloud.cn/1.1/verifySmsCode/$code?mobilePhoneNumber=$phone")
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'X-LC-Id' => Yii::$app->params['lean_cloud_id'],
                'X-LC-Key' => Yii::$app->params['lean_cloud_key'],
            ])
            ->send();
        if ($response->isOk) {
            return 0;
        } else {
            return $response->getData()['error'];
        }
    }
}