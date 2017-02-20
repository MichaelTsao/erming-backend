<?php

namespace app\controllers;

use app\models\Range;

class RangeController extends \yii\rest\Controller
{
    public function actionList()
    {
        return Range::find()->select(['concat(min, "-", max)', 'id'])->indexBy('id')->column();
    }
}
