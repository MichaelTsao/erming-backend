<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hospital".
 *
 * @property integer $id
 * @property string $name
 */
class Hospital extends \yii\db\ActiveRecord
{
    protected static $_names = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hospital';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名字',
        ];
    }

    public static function names()
    {
        if (static::$_names === null) {
            static::$_names = static::find()->select(['name', 'id'])->indexBy('id')->column();
        }
        return static::$_names;
    }
}
