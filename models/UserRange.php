<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_range".
 *
 * @property integer $user_id
 * @property integer $range_id
 * @property integer $pay_id
 * @property string $ctime
 */
class UserRange extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_range';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'range_id'], 'required'],
            [['user_id', 'range_id', 'pay_id'], 'integer'],
            [['ctime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'range_id' => 'Range ID',
            'pay_id' => 'Pay ID',
            'ctime' => 'Ctime',
        ];
    }
}
