<?php

namespace app\controllers;

use app\models\base\Common;
use app\models\User;
use app\models\UserRange;
use Yii;
use yii\web\ForbiddenHttpException;
use app\models\SmsCode;

class UserController extends \yii\rest\Controller
{
    public function actionLogin($code)
    {
        if (!Yii::$app->weixin->getAppAuth($code)) {
            throw new ForbiddenHttpException();
        }

        if (!$uid = User::loginByWeixin(Yii::$app->weixin->openId)) {
            throw new ForbiddenHttpException();
        }

        return ['token' => User::setToken($uid), 'info' => User::findOne($uid)];
    }

    public function actionAuth($token)
    {
        if ($user = User::findIdentityByAccessToken($token)) {
            return $user;
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

        $weixin = new WeiXin([
            'appId' => Yii::$app->params['weixin_appid'],
            'appSecret' => Yii::$app->params['weixin_secret'],
        ]);

        $open_id = '';
        if ($weixinInfo = $weixin->codeToSession(Yii::$app->request->post('weixin_code'))) {
            $open_id = $weixinInfo['openid'];
        }

        $user = new User();
        $user->phone = Yii::$app->request->post('phone');
        $user->password = md5(Yii::$app->request->post('password'));
        $user->name = Yii::$app->request->post('name');
        $user->hospital_id = Yii::$app->request->post('hospital');
        $user->open_id = $open_id;

        if (!$user->save()) {
            return [2, "系统错误"];
        }

        if (!$token = User::setToken($user->id)) {
            return [2, "系统错误"];
        }

        return [0, $token, $user];
    }

    public function actionSetRange($rangeId, $token)
    {
        if (!$user = User::findIdentityByAccessToken($token)) {
            throw new ForbiddenHttpException();
        }

        $range = new UserRange();
        $range->user_id = $user->id;
        $range->range_id = $rangeId;
        $range->save();
        return 0;
    }
}
