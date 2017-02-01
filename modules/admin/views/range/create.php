<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Range */

$this->title = Yii::t('app', 'Create Range');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Ranges'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="range-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
