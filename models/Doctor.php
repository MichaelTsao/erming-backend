<?php

namespace app\models;

use dosamigos\qrcode\lib\Enum;
use dosamigos\qrcode\QrCode;
use Yii;

/**
 * This is the model class for table "doctor".
 *
 * @property integer $id
 * @property string $name
 * @property integer $hospital_id
 * @property string $ctime
 * @property string $promoteQr
 */
class Doctor extends \yii\db\ActiveRecord
{
    public $qr = null;
    protected static $_names = null;

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
            'qr' => '推广二维码',
        ];
    }

    public function getPromoteQr()
    {
        $fileName = "file/promote_" . $this->id . ".png";
        $file = Yii::getAlias("@webroot/$fileName");
        if (!file_exists($file)) {
            QrCode::png(
                'http://' . Yii::$app->request->serverName . '/doctor/promote/' . $this->id,
                $file,
                Enum::QR_ECLEVEL_L,
                7,
                2
            );
        }
        return 'http://' . Yii::$app->request->serverName . '/' . $fileName;
    }

    public static function names()
    {
        if (static::$_names === null) {
            static::$_names = static::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
        }
        return static::$_names;
    }
}
