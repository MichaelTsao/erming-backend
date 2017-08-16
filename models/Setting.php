<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "setting".
 *
 * @property string $id
 * @property string $name
 * @property string $value
 */
class Setting extends \yii\db\ActiveRecord
{
    const TRIAL_TYPE_MINUTE = 1;
    const TRIAL_TYPE_HOUR = 2;

    public static $trailType = [
        self::TRIAL_TYPE_MINUTE => '分钟',
        self::TRIAL_TYPE_HOUR => '小时',
    ];

    public $trial_length;
    public $trial_type;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 50],
            [['value'], 'string', 'max' => 200],
            [['trial_length', 'trial_type'], 'safe'],
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
            'value' => '取值',
            'trial_length' => '取值',
            'trial_type' => '类型',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->id == 'trial_length') {
            $this->value = json_encode(['length' => $this->trial_length, 'type' => $this->trial_type]);
            return true;
        }
        return false;
    }

    public function afterFind()
    {
        if ($this->id == 'trial_length') {
            if ($info = json_decode($this->value)) {
                if (isset($info->type)) {
                    $this->trial_type = $info->type;
                }
                if (isset($info->length)) {
                    $this->trial_length = $info->length;
                }
            }
        }
    }
}
