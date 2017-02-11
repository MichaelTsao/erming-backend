<?php

if (YII_ENV_DEV) {
    return [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 3,
    ];
}

if (YII_ENV_TEST) {
    return [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 3,
    ];
}

if (YII_ENV_PROD) {
    return [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 3,
    ];
}
