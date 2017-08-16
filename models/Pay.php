<?php

namespace app\models;

/**
 * This is the model class for table "pay".
 *
 * @property integer $pay_id
 * @property integer $user_id
 * @property string $transaction_number
 * @property string $price
 * @property string $ctime
 * @property string $pay_time
 * @property string $vendor_str
 */
class Pay extends \yii\db\ActiveRecord
{
    const STATUS_NOT_PAY = 1;
    const STATUS_PAYED = 2;
    const STATUS_PAY_FAIL = 3;
    const STATUS_REFUND = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['price'], 'number'],
            [['ctime', 'pay_time', 'vendor_str'], 'safe'],
            [['transaction_number'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pay_id' => 'Pay ID',
            'user_id' => '用户',
            'transaction_number' => '交易单号',
            'price' => '价格',
            'ctime' => '创建时间',
            'pay_time' => '支付时间',
            'vender_str' => '交易串',
        ];
    }
}
