<?php

namespace app\controllers;

use app\models\Hospital;

class HospitalController extends \yii\rest\Controller
{
    public function actionList()
    {
        return Hospital::find()->select(['name', 'id'])->indexBy('id')->column();
    }
}
