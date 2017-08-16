<?php

namespace app\controllers;

use app\models\Setting;

class SettingController extends \yii\rest\Controller
{
    public function actionIndex()
    {
        return Setting::find()->select(['value', 'id'])->indexBy('id')->column();
    }
}
