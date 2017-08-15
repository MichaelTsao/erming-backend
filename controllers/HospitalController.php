<?php

namespace app\controllers;

use app\models\Hospital;

class HospitalController extends \yii\rest\Controller
{
    public function actionIndex()
    {
        return Hospital::find()->select(['name', 'id'])->indexBy('id')->column();
    }
}
