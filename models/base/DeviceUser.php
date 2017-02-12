<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "device_user".
 *
 * @property string $device_id
 * @property integer $uid
 * @property integer $type
 * @property string $ctime
 */
class DeviceUser extends CachedActiveRecord
{
    const TYPE_NULL = 0;
    const TYPE_PC = 1;
    const TYPE_ANDROID = 2;
    const TYPE_IPHONE = 3;
    const TYPE_IPAD = 4;
    const TYPE_H5 = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'device_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'uid'], 'required'],
            [['uid', 'type'], 'integer'],
            [['uid'], 'integer', 'min' => 1],
            [['ctime'], 'safe'],
            [['device_id'], 'string', 'max' => 200],
            [['device_id'], 'string', 'min' => 5],
            ['type', 'default', 'value' => self::TYPE_NULL],
            ['type', 'in', 'range' => [self::TYPE_NULL, self::TYPE_PC, self::TYPE_ANDROID, self::TYPE_IPAD,
                self::TYPE_IPHONE, self::TYPE_H5]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'device_id' => '设备',
            'uid' => '用户',
            'type' => '设备类型',
            'ctime' => '创建时间',
        ];
    }

    public static function create($uid, $device_id, $platform)
    {
        if (!$device_user = static::findOne(['device_id' => $device_id])) {
            $class = self::className();
            $device_user = new $class;
            $device_user->device_id = strval($device_id);
            $device_user->type = $platform;
        }
        $device_user->uid = $uid;
        if ($device_user->validate() && $device_user->save()) {
            return true;
        } else {
            return false;
        }
    }

    public static function remove($uid, $device_id)
    {
        if (!$device_id || !$uid) {
            return false;
        }
        static::deleteAll(['device_id' => $device_id, 'uid' => $uid]);
    }
}
