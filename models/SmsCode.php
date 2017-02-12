<?php
/**
 * Created by PhpStorm.
 * User: caoxiang
 * Date: 16/1/5
 * Time: 下午3:57
 */

namespace app\models;

use Yii;
use yii\base\Model;

class SmsCode extends Model
{
    const SCENARIO_SEND = 'send';
    const SCENARIO_CHECK = 'check';

    const TYPE_REGISTER = 1;
    const TYPE_CHANGE_PHONE = 2;
    const TYPE_RESET_PASSWORD = 3;

    public $phone;
    public $uid;
    public $type;
    public $code;

    protected $limit = 10;
    protected $exist_time = 10;
    protected $_cachedCode;

    public function scenarios()
    {
        return [
            self::SCENARIO_SEND => ['uid', 'phone', 'type'],
            self::SCENARIO_CHECK => ['uid', 'phone', 'type', 'code'],
        ];
    }

    public function rules()
    {
        return [
            [['phone', 'code', 'type'], 'required'],

            ['uid', 'integer'],
            ['uid', 'default', 'value' => 0],

            [['phone'], 'string', 'max' => 20],
            [['phone'], 'string', 'min' => 11],

            [['code'], 'string', 'length' => 6],

            ['type', 'in', 'range' => [self::TYPE_REGISTER, self::TYPE_CHANGE_PHONE, self::TYPE_RESET_PASSWORD]],
            ['type', 'default', 'value' => self::TYPE_REGISTER],
        ];
    }

    public function getCodeKey()
    {
        return 'phone_confirm:' . $this->uid . ':' . $this->phone;
    }

    public function getLimitKey()
    {
        return 'sms_limit:' . $this->uid . ':' . $this->phone . ':' . date('Ymd');
    }

    public function getCachedCode()
    {
        if (!$this->_cachedCode) {
            $this->_cachedCode = Yii::$app->redis->get($this->codeKey);
        }
        return $this->_cachedCode;
    }

    public function setCachedCode($code)
    {
        $this->_cachedCode = $code;
        return Yii::$app->redis->set($this->codeKey, $code, 60 * $this->exist_time);
    }

    public function send()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = User::findByPhone($this->phone);
        if (in_array($this->type, [self::TYPE_REGISTER, self::TYPE_CHANGE_PHONE]) && $user) {
            $this->addError('phone', '手机号已经注册');
            return false;
        }
        if (in_array($this->type, [self::TYPE_RESET_PASSWORD]) && !$user) {
            $this->addError('phone', '此手机号未绑定用户');
            return false;
        }

        if (intval(Yii::$app->redis->get($this->limitKey)) > $this->limit) {
            $this->addError('phone', '此手机号今日已达到申请上限');
            return false;
        }

        Sms::send($this->phone, Sms::SEND_CODE);

        Yii::$app->redis->incr($this->limitKey);
        Yii::$app->redis->expire($this->limitKey, 600);
        return true;
    }

    public function check()
    {
        if (!$this->validate()) {
            return false;
        }
        return Sms::verify($this->phone, $this->code);
    }
}