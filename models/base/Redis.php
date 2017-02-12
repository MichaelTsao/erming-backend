<?php
/**
 * Created by PhpStorm.
 * User: caoxiang
 * Date: 16/1/4
 * Time: 上午12:18
 */

namespace app\models\base;

use yii\redis\Connection;

class Redis extends Connection
{
    public $keyPrefix = null;

    public function makeKey($key)
    {
        return $this->keyPrefix && $key ? $this->keyPrefix . "." . $key : $key;
    }

    public function get($key, $subKey = null)
    {
        if (!$key) {
            return false;
        }
        if ($subKey) {
            return $this->executeCommand('HGET', [$this->makeKey($key), $subKey]);
        } else {
            return $this->executeCommand('GET', [$this->makeKey($key)]);
        }
    }

    public function set($key, $values, $expire = 0)
    {
        if (!$key) {
            return false;
        }
        if (is_array($values)) {
            $setArgs = [$this->makeKey($key)];
            foreach ($values as $attribute => $value) {
                if ($value !== null) {
                    if (is_bool($value)) {
                        $value = (int)$value;
                    }
                    $setArgs[] = $attribute;
                    $setArgs[] = $value;
                }
            }
            if (count($setArgs) > 1) {
                return $this->executeCommand('HMSET', $setArgs);
            }
            return false;
        } else {
            if ($expire) {
                return $this->executeCommand('SETEX', [$this->makeKey($key), $expire, $values]);
            } else {
                return $this->executeCommand('SET', [$this->makeKey($key), $values]);
            }
        }
    }

    public function incr($key, $value = 1)
    {
        if (!$key) {
            return false;
        }
        return $this->executeCommand('INCRBY', [$this->makeKey($key), $value]);
    }

    public function getHash($key)
    {
        if (!$key) {
            return false;
        }
        $r = $this->executeCommand('HGETALL', [$this->makeKey($key)]);
        if ($r) {
            $result = [];
            for ($i = 0; $i < count($r); $i += 2) {
                $result[$r[$i]] = $r[$i + 1];
            }
            return $result;
        }
        return false;
    }

    public function del($key)
    {
        if (!$key) {
            return false;
        }
        return $this->executeCommand('DEL', [$this->makeKey($key)]);
    }

    public function delAll($pattern)
    {
        $cmd = "return redis.call('del', unpack(redis.call('keys', '" . $this->makeKey($pattern) . "')))";
        return $this->executeCommand('EVAL', [$cmd]);
    }

    public function expire($key, $time)
    {
        return $this->executeCommand('EXPIRE', [$this->makeKey($key), $time]);
    }

    public function zRange($key, $start = 0, $end = -1, $sort = 0)
    {
        if ($sort) {
            $cmd = 'ZRANGE';
        } else {
            $cmd = 'ZREVRANGE';
        }
        return $this->executeCommand($cmd, [$this->makeKey($key), $start, $end]);
    }

    public function zAdd($key, $value, $score)
    {
        return $this->executeCommand('ZADD', [$this->makeKey($key), $score, $value]);
    }

    public function zRem($key, $value)
    {
        return $this->executeCommand('ZREM', [$this->makeKey($key), $value]);
    }
}