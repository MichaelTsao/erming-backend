<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Hospital;

/* @var $this yii\web\View */
/* @var $model app\models\Doctor */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="doctor-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'hospital_id')->dropDownList(Hospital::names()) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '新建' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
