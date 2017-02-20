<?php
/**
 * Created by PhpStorm.
 * User: caoxiang
 * Date: 16/3/17
 * Time: 下午12:28
 */

namespace app\models\base;

use Yii;

class Common
{
    public static function request($url, $param = array(), $header = array(), $ssl = false, $files = [], $custom=false)
    {
        $ch = curl_init();
        if ($custom) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom);
        }
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 10,
        );
        if ($param) {
            $options[CURLOPT_POST] = 1;
            if (is_array($param)) {
                $options[CURLOPT_POSTFIELDS] = http_build_query($param);
            } else {
                $options[CURLOPT_POSTFIELDS] = $param;
            }
        }
        if ($header) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        if ($ssl) {
            if ($files && isset($files['cert']) && isset($files['key'])) {
                curl_setopt($ch, CURLOPT_SSLCERT, $files['cert']);
                curl_setopt($ch, CURLOPT_SSLKEY, $files['key']);
            } else {
                $options[CURLOPT_SSL_VERIFYPEER] = false;
                $options[CURLOPT_SSL_VERIFYHOST] = false;
                $options[CURLOPT_SSLVERSION] = 1;
            }
        }
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function camelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    public static function getFirstError($object)
    {
        $values = array_values($object->firstErrors);
        return isset($values[0]) ? $values[0] : '';
    }

    static public function uploadImage($type, $uid)
    {
        if (!isset($_POST[$type])) {
            return array(51, '文件不存在');
        }
        $file = $_POST[$type];
        $file = base64_decode($file);
        $new_name = "i_" . $uid . "_" . md5(time() . rand(100, 999)) . '.jpg';
        $new_file = Yii::getAlias("@webroot/$type/$new_name");
        $byte = file_put_contents($new_file, $file, LOCK_EX);//返回的是字节数
        return $byte ? [0, $new_name] : [53, "请检查文件的尺寸和大小！"];
    }

    public static function getChatInfo($id)
    {
        //获取聊天信息
        $chat_info = self::request('https://leancloud.cn/1.1/rtm/messages/logs?convid=' . $id . '&limit=1000', array(), array(
            'X-LC-Id: ' . Yii::$app->params['lean_cloud_id'],
            'X-LC-Key: ' . Yii::$app->params['lean_cloud_master'] . ',master'
        ));
        return $chat_info;
    }

    public static function sendMsg($chat_id, $uid, $target, $msg)
    {
        $r = self::request('https://leancloud.cn/1.1/rtm/messages',
            json_encode(array(
                'conv_id' => $chat_id,
                'from_peer' => $uid,
                'message' => json_encode(['_lctype' => -1, '_lctext' => $msg]),
//                'no_sync' => 'true',
                'transient' => false
            )),
            array(
                'X-LC-Id: ' . Yii::$app->params['lean_cloud_id'],
                'X-LC-Key: ' . Yii::$app->params['lean_cloud_master'] . ",master",
                'Content-Type: application/json',
            ));
        Yii::warning('lean cloud msg: ' . json_encode([
                'id' => Yii::$app->params['lean_cloud_id'], 'master' => Yii::$app->params['lean_cloud_master'],
                'chat_id' => $chat_id, 'uid' => $uid, 'target' => $target, 'msg' => $msg, 'result' => $r
            ]));
        return $r;
    }

    public static function addChatUser($chat_id, $uid)
    {
        $r = self::request('https://api.leancloud.cn/1.1/classes/_Conversation/' . $chat_id,
            json_encode(array(
                'm' => [
                    '__op' => 'AddUnique',
                    'objects' => [$uid],
                ],
            )),
            array(
                'X-LC-Id: ' . Yii::$app->params['lean_cloud_id'],
                'X-LC-Key: ' . Yii::$app->params['lean_cloud_master'] . ",master",
                'Content-Type: application/json',
            ), false, [], 'PUT');
        return $r;
    }

    public static function makeXML($param)
    {
        if (!is_array($param)
            || count($param) <= 0
        ) {
            return false;
        }

        $xml = "<xml>";
        foreach ($param as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public static function getOrderId()
    {
        $rand24 = mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999);
        $rand8 = substr($rand24, mt_rand(0, 16), 8);
        return date('ymd') . str_pad($rand8, 8, '0', STR_PAD_LEFT);
    }

    public static function getId()
    {
        return substr(date('ymd'), 1) . rand(10000, 99999);
    }

    public static function showArrayValue($array, $key)
    {
        return isset($array[$key]) ? $array[$key] : '';
    }

    public static function setEcho(&$var, $default = '')
    {
        return isset($var) ? (empty($var) ? $default : $var) : $default;
    }

    public static function setObjEcho($var, $default = '')
    {
        return isset($var) ? (empty($var) ? $default : $var) : $default;
    }
}