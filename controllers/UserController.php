<?php

namespace app\controllers;

use app\models\base\Common;
use app\models\User;
use Yii;
use mycompany\common\WeiXin;
use yii\web\ForbiddenHttpException;
use app\models\SmsCode;

class UserController extends \yii\rest\Controller
{
    public function actionLoginWx($code)
    {
        $weixin = new WeiXin([
            'appId' => Yii::$app->params['weixin_appid'],
            'appSecret' => Yii::$app->params['weixin_secret'],
        ]);

        if (!$weixinInfo = $weixin->codeToSession($code)) {
            throw new ForbiddenHttpException();
        }

        if (!$token = User::loginByWeixin($weixinInfo['openid'])) {
            return -1;
        }

        return $token;
    }

    public function acitonLogin($token)
    {
        if ($user = User::findIdentityByAccessToken($token)){
            return $user->id;
        }
        return 0;
    }

    public function actionRegister()
    {
        $sms_code_config = [
            'phone' => Yii::$app->request->post('phone', ''),
            'code' => Yii::$app->request->post('code', ''),
            'type' => SmsCode::TYPE_REGISTER,
            'scenario' => SmsCode::SCENARIO_CHECK,
        ];
        $sms_code = new SmsCode($sms_code_config);
        if (!$sms_code->check()) {
            return [1, Common::getFirstError($sms_code)];
        }

        $user = new User();
        $user->phone = Yii::$app->request->post('phone');
        $user->password = Yii::$app->request->post('password');
        $user->name = Yii::$app->request->post('name');
        $user->hospital_id = Yii::$app->request->post('hospital_id');

        $passwordAgain = Yii::$app->request->post('passwordAgain');

        if (!$user->save()) {
            return -1;
        }

        return [0, $token];
    }

}
