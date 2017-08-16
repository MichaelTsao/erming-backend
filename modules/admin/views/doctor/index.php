<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Hospital;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DoctorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '医生';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="doctor-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('新建医生', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'hospital_id',
                'content' => function ($data, $row) {
                    return isset(Hospital::names()[$data->hospital_id]) ? Hospital::names()[$data->hospital_id] : '';
                },
                'filter' => Hospital::names(),
            ],

            'ctime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
