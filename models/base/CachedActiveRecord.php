<?php
/**
 * Created by PhpStorm.
 * User: caoxiang
 * Date: 16/3/19
 * Time: 下午6:56
 */

namespace app\models\base;

use yii\db\ActiveRecord;
use Yii;

abstract class CachedActiveRecord extends ActiveRecord
{
    protected $_cacheKey = null;

    public static function cache($key)
    {
        $record = Yii::createObject(static::className());

        $primaryKey = static::primaryKey();
        if (isset($primaryKey[0])) {
            $record->attributes = [$primaryKey[0] => $key];
        }

        if (!$record->loadByCache($key)) {
            if ($record = static::loadByDb($key)) {
                $record->writeCache();
            } else {
                return false;
            }
        }
        return $record;
    }

    public static function loadByDb($key)
    {
        return static::findOne($key);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->writeCache();
    }

    public function afterDelete()
    {
        if ($this->cacheKey) {
            Yii::$app->redis->del($this->cacheKey);
        }
    }

    public function writeCache()
    {
        Yii::$app->redis->set($this->cacheKey, $this->attributes);
    }

    public function getCacheKey()
    {
        if (!$this->_cacheKey) {
            if (is_array($this->primaryKey)) {
                $key = implode('_', array_values($this->primaryKey));
            } else {
                $key = $this->primaryKey;
            }
            if ($key) {
                $reflect = new \ReflectionClass($this);
                $this->_cacheKey = Common::camelCase($reflect->getShortName()) . ':' . $key;
            }
        }
        return $this->_cacheKey;
    }

    public function loadByCache()
    {
        if ($info = Yii::$app->redis->getHash($this->cacheKey)) {
            $this->attributes = $info;
            $this->isNewRecord = false;
            return true;
        } else {
            return false;
        }
    }
}