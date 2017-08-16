<?php

namespace app\controllers;


class DoctorController extends \yii\web\Controller
{
    public function actionPromote($id = 0)
    {
        return $this->redirect(['/']);
    }
}
