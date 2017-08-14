<?php

namespace app\controllers;

use app\models\Range;

class RangeController extends \yii\rest\Controller
{
    public function actionIndex()
    {
        return Range::find()->all();
    }
}
