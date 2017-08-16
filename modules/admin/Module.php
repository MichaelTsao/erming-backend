<?php

namespace app\modules\admin;

use Yii;
use yii\web\Response;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->layout = 'default';
        Yii::$app->response->format = Response::FORMAT_HTML;
        // custom initialization code goes here
    }
}
