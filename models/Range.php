<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "range".
 *
 * @property integer $id
 * @property integer $min
 * @property integer $max
 * @property string $file
 * @property \yii\web\UploadedFile $fileModel
 */
class Range extends \yii\db\ActiveRecord
{
    public $fileModel = null;

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
            [['fileModel'], 'file', 'skipOnEmpty' => false, 'extensions' => 'mp3', 'maxSize' => 1024 * 1024 * 10],
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
            'fileModel' => '音频文件',
        ];
    }

    public function beforeValidate()
    {
        foreach ($this->validators as $item) {
            $reflect = new \ReflectionClass($item);
            if ($reflect->getShortName() == 'FileValidator') {
                foreach ($item->attributes as $key) {
                    $this->$key = UploadedFile::getInstance($this, $key);
                }
            }
        }

        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        foreach ($this->validators as $item) {
            $reflect = new \ReflectionClass($item);
            if ($reflect->getShortName() == 'FileValidator') {
                foreach ($item->attributes as $key) {
                    $url_key = str_replace('Model', '', $key);
                    if ($this->$key && $this->validate([$key], false)) {
                        $file = "/$url_key/" . md5($this->$key->baseName . rand(100, 999))
                            . '.' . $this->$key->extension;
                        $this->$url_key = $file;
                        $this->$key->saveAs(Yii::getAlias('@webroot') . $file);
                    }else{
                        Yii::warning('debug:'.json_encode($this->errors));
                    }
                }
            }
        }

        return parent::beforeSave($insert);
    }
}
