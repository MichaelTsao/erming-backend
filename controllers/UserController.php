<?php

namespace app\controllers;

use app\models\base\Common;
use app\models\Member;
use app\models\SmsCode;
use app\models\User;
use app\models\UserRange;
use Yii;
use yii\filters\auth\QueryParamAuth;
use yii\web\ForbiddenHttpException;

class UserController extends \yii\rest\Controller
{
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => QueryParamAuth::className(),
                'only' => ['member', 'set-range', 'buy'],
            ],
        ];
    }

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

    public function actionIndex()
    {
        $sms_code_config = [
            'phone' => Yii::$app->request->post('phone', ''),
            'code' => Yii::$app->request->post('code', ''),
            'type' => SmsCode::TYPE_REGISTER,
            'scenario' => SmsCode::SCENARIO_CHECK,
        ];
        $sms_code = new SmsCode($sms_code_config);
        if ($error = $sms_code->check()) {
            throw new ForbiddenHttpException($error);
        }

        $open_id = '';
        if (Yii::$app->weixin->getAppAuth(Yii::$app->request->post('weixin_code'))) {
            $open_id = Yii::$app->weixin->openId;
        }

        $user = new User();
        $user->phone = Yii::$app->request->post('phone');
        $user->name = Yii::$app->request->post('name');
        if ($doctorId = Yii::$app->request->post('doctor', 0)) {
            $user->doctor_id = $doctorId;
        }
        $user->open_id = $open_id;

        if (!$user->save()) {
            throw new ForbiddenHttpException(Common::getFirstError($user));
        }

        if (!$token = User::setToken($user->id)) {
            throw new ForbiddenHttpException("系统错误");
        }

        return ['token' => $token, 'info' => $user];
    }

    public function actionSetRange($rangeId)
    {
        $range = new UserRange();
        $range->user_id = Yii::$app->user->id;
        $range->range_id = $rangeId;
        $range->save();
        return 0;
    }

    public function actionSendCode($phone)
    {
        if (isset(Yii::$app->user->id)) {
            $uid = Yii::$app->user->id;
        } else {
            $uid = 0;
        }
        $sms_code_config = [
            'uid' => $uid,
            'phone' => $phone,
            'type' => Yii::$app->request->get('type', SmsCode::TYPE_REGISTER),
            'scenario' => SmsCode::SCENARIO_SEND,
        ];
        $sms_code = new SmsCode($sms_code_config);
        if (!$sms_code->send()) {
            throw new ForbiddenHttpException(Common::getFirstError($sms_code));
        }
    }

    public function actionMember()
    {
        if (!Member::check(Yii::$app->user->id)) {
            throw new ForbiddenHttpException('用户无权限使用服务');
        }
        return 'ok';
    }

    public function actionBuy()
    {
        return false;
    }
}
