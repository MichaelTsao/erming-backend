<?php

namespace app\controllers;

use app\models\User;
use Yii;
use mycompany\common\WeiXin;
use yii\web\ForbiddenHttpException;

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
        $user = new User();
        $user->phone = Yii::$app->request->post('phone');
        $user->phoneCode = Yii::$app->request->post('phoneCode');
        $user->password = Yii::$app->request->post('password');
        $user->name = Yii::$app->request->post('name');
        $user->hospital_id = Yii::$app->request->post('hospital_id');

        $passwordAgain = Yii::$app->request->post('passwordAgain');

        if (!$user->save()) {
            return -1;
        }

        return $token;
    }

}
