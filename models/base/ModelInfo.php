<?php
/**
 * Created by PhpStorm.
 * User: caoxiang
 * Date: 16/4/14
 * Time: 下午6:27
 */

namespace app\models\base;

use yii\base\Behavior;

class ModelInfo extends Behavior
{
    public function info()
    {
        $data = $this->owner->toArray();
        foreach ($this->owner->validators as $item) {
            $reflect = new \ReflectionClass($item);
            if ($reflect->getShortName() == 'StringValidator') {
                foreach ($item->attributes as $key) {
                    if (array_key_exists($key, $data)) {
                        $data[$key] = strval($data[$key]);
                    }
                }
            }
            if ($reflect->getShortName() == 'NumberValidator' && $item->integerOnly) {
                foreach ($item->attributes as $key) {
                    if (array_key_exists($key, $data)) {
                        $data[$key] = intval($data[$key]);
                    }
                }
            }
        }
        return $data;
    }
}