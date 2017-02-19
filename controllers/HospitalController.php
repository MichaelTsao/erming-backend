<?php

namespace app\controllers;

use app\models\Hospital;
use app\models\User;
use Yii;
use yii\web\ForbiddenHttpException;

class HospitalController extends \yii\rest\Controller
{
    public function actionList()
    {
        return Hospital::find()->select(['name', 'id'])->indexBy('id')->column();
    }
}
