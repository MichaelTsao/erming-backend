<?php

namespace app\controllers;

use Yii;

class UserController extends \yii\rest\Controller
{
    public function actionWxLogin($code)
    {
        $appId = Yii::$app->params['weixin_app_id'];
        $secret = 'f5214b4c4e803229d524b844b640cd26';

        $r = Logic::request("https://api.weixin.qq.com/sns/jscode2session?appid=$appId&secret=$secret&js_code=$code&grant_type=authorization_code");
        if (!$r) {
            throw new ApiException(ApiException::LOGIN_FAIL);
        }

        $result = json_decode($r, true);
        if (!isset($result['openid'])) {
            throw new ApiException(ApiException::LOGIN_FAIL);
        }

        $openId = $result['openid'];
        $session = $result['session_key'];

        if (!$wxUser = WxUser::model()->findByPk($openId)) {
            $user = new UserDB();
            $user->status = 1;
            $user->save();

            $wxUser = new WxUser();
            $wxUser->openid = $openId;
            $wxUser->session_key = $session;
            $wxUser->uid = $user->uid;
            $wxUser->save();
        } else {
            $wxUser->session_key = $session;
            $wxUser->save();
        }

        $token = User::makeToken($openId);

        $token_info = new UserToken();
        $token_info->uid = $wxUser->uid;
        $token_info->token = $token;
        $token_info->save();

        Logic::makeResult($token);
    }

    public function actionRegister()
    {
        return $this->render('register');
    }

}
