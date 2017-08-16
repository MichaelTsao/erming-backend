<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "doctor".
 *
 * @property integer $id
 * @property string $name
 * @property integer $hospital_id
 * @property string $ctime
 */
class Doctor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'doctor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hospital_id'], 'integer'],
            [['ctime'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'required'],
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
            'hospital_id' => '医院',
            'ctime' => '创建时间',
        ];
    }
}
