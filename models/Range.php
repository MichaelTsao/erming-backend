<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "range".
 *
 * @property integer $id
 * @property integer $min
 * @property integer $max
 * @property string $file
 */
class Range extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'range';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['min', 'max'], 'integer'],
            [['file'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '频段ID',
            'min' => '频段下限',
            'max' => '频段上限',
            'file' => '音频文件',
        ];
    }
}
