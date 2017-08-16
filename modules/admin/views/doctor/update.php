<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Doctor */

$this->title = '修改医生: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '医生', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="doctor-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
