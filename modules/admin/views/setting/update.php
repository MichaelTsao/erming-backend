<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Setting */

$this->title = '修改配置: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '配置', 'url' => ['index']];
$this->params['breadcrumbs'][] = '修改';
?>
<div class="setting-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
