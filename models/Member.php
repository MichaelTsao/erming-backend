<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $pay_id
 * @property string $ctime
 * @property string $end_time
 */
class Member extends \yii\db\ActiveRecord
{
    const TRIAL = 'trial';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'pay_id'], 'integer'],
            [['ctime', 'end_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户',
            'pay_id' => '支付订单',
            'ctime' => '创建时间',
            'end_time' => '结束时间',
        ];
    }

    public static function create($userId, $payId)
    {
        if ($payId == static::TRIAL) {
            $interval = 60;
            $amount = 5;
            if ($setting = Setting::findOne('trial_length')) {
                if ($setting->trial_type == Setting::TRIAL_TYPE_HOUR) {
                    $interval = 3600;
                }
                if (intval($setting->trial_length) > 0) {
                    $amount = intval($setting->trial_length);
                }
            }
            $endTime = time() + $amount * $interval;
        } else {
            $endTime = time() + 30 * 86400;
        }
        $member = new static();
        $member->user_id = $userId;
        $member->pay_id = $payId;
        $member->end_time = date('Y-m-d H:i:s', $endTime);
        return $member->save();
    }
}
