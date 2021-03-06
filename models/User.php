<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $phone
 * @property string $name
 * @property string $password
 * @property integer $doctor_id
 * @property integer $range_id
 * @property string $open_id
 * @property Doctor $doctor
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $username;
    public $authKey;
    public $accessToken;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['doctor_id', 'range_id'], 'integer'],
            [['phone'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 100],
            [['password'], 'string', 'max' => 50],
            [['open_id'], 'string', 'max' => 50],
            [['phone', 'name'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '手机',
            'name' => '名字',
            'password' => '密码',
            'doctor_id' => '医院',
            'range_id' => '频段',
            'open_id' => '微信Open ID',
        ];
    }

    public function getDoctor()
    {
        return $this->hasOne(Doctor::className(), ['id' => 'doctor_id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($uid = Yii::$app->redis->get('user_token:' . $token)) {
            return static::findIdentity($uid);
        }

        return null;
    }

    public static function findByPhone($phone)
    {
        return static::findOne(['phone' => $phone]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['name' => $username]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }

    public static function loginByWeixin($openId)
    {
        if (!$user = static::findOne(['open_id' => $openId])) {
            return 0;
        }
        if (empty($user->phone)) {
            return 0;
        }
        return $user->id;
    }

    public static function setToken($user_id)
    {
        $token = md5(time() . $user_id . rand(100, 999));

        Yii::$app->redis->setex('user_token:' . $token, 86400 * 30, $user_id);

        return $token;
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'range_id',
        ];
    }

    public function afterSave($insert, $attr)
    {
        if ($insert) {
            Member::create($this->id, Member::TRIAL);
        }
    }
}
