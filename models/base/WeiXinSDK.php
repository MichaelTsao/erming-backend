<?php
/**
 * Created by PhpStorm.
 * User: caoxiang
 * Date: 15/9/21
 * Time: 下午6:46
 */

namespace app\models\base;

use yii\base\Object;
use Yii;

class WeiXinSDK extends Object
{
    const OK = 1;
    const NEED_REDIRECT = 2;

    public $appId = '';
    public $appSecret = '';
    public $appMchId = '';
    public $appPayKey = '';
    public $certFile = '';
    public $certKey = '';

    public $test_rate = 100;   // 微信钱用整数表示,单位是分,用此参数调整实际付费金额,一般测试时会修改
    public $auth_url = null;
    public $sns_token = '';
    public $redirect_url = '';
    public $openId = null;

    private $_isWeixin = null;
    private $_isSub = null;

    /*
     * 更新Token
     * 目前设置token在缓存存在一小时,建议每半小时更新一次token,一天可以调用2000次
     */
    public function setAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
        $res = json_decode($this->httpGet($url));
        $access_token = $res->access_token;
        if ($access_token) {
            Yii::$app->redis->set('wx_token', $access_token, 3600);
        }
    }

    /*
     * 获取Token
     */
    public function getAccessToken()
    {
        $access_token = Yii::$app->redis->get('wx_token');
        if (!$access_token) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                Yii::$app->redis->set('wx_token', $access_token, 3600);
            }
        }
        return $access_token;
    }

    /*
     * HTTP Get 请求
     */
    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    /*
     * 生成随机串
     */
    private function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /*
     * 计算签名
     */
    private function makeSign($values)
    {
        ksort($values);
        $buff = "";
        foreach ($values as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $string = trim($buff, "&");
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->appPayKey;
        Yii::warning('wx_pay_string:' . $string);
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /*
     * 发起支付
     */
    public function payRequest($open_id, $order_id, $price, $back_url)
    {
        if (!$open_id) {
            return false;
        }

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $param = [
            'body' => '大咖说',
            'attach' => 'theattach',
            'out_trade_no' => $order_id,
            'total_fee' => $price * $this->test_rate,
            'time_start' => date("YmdHis"),
            'time_expire' => date("YmdHis", time() + 600),
            'goods_tag' => 'a_goods_tag',
            'notify_url' => $back_url,
            'trade_type' => 'JSAPI',
            'openid' => $open_id,
            'appid' => $this->appId,
            'mch_id' => $this->appMchId,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'nonce_str' => $this->getNonceStr(),
        ];
        $param['sign'] = $this->makeSign($param);
        $xml = Common::makeXML($param);
        Yii::warning('wxpay_from:' . $xml);
        $data = Common::request($url, $xml, [], true);
        Yii::warning('wxpay_back:' . $data);
        $result = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($result['result_code'] != 'SUCCESS') {
            Yii::warning('wxpay_fail');
            return false;
        }

        $js_param = [
            'appId' => $result['appid'],
            'timeStamp' => strval(time()),
            'nonceStr' => $this->getNonceStr(),
            'package' => 'prepay_id=' . $result['prepay_id'],
            'signType' => 'MD5',
        ];
        $js_param['paySign'] = $this->makeSign($js_param);
        Yii::warning('wxpay_result:' . json_encode($js_param));
        return json_encode($js_param);
    }

    /*
     * 确认支付状态
     */
    public function payConfirm()
    {
        $check_result = false;
        $vender_str = '';
        $out_trade_no = '';
        $trade_no = '';

        $postdata = file_get_contents("php://input");
        Yii::warning('wx_result:' . $postdata);
        $xml = simplexml_load_string($postdata);
        if ((string)$xml->return_code[0] == 'SUCCESS' && (string)$xml->result_code[0] == 'SUCCESS') {
            $vender_str = $postdata;
            $out_trade_no = (string)$xml->out_trade_no[0];
            if (strstr($out_trade_no, '_')) {
                $tmp = explode('_', $out_trade_no);
                $out_trade_no = $tmp[0];
            }
            $trade_no = (string)$xml->transaction_id[0];

            $rstr = $this->getNonceStr();
            $sign = strtoupper(md5("appid=" . $this->appId . "&mch_id=" . $this->appMchId . "&nonce_str=$rstr&transaction_id=$trade_no&key=" . $this->appPayKey));
            $data = "<xml>
                           <appid>" . $this->appId . "</appid>
                           <mch_id>" . $this->appMchId . "</mch_id>
                           <nonce_str>$rstr</nonce_str>
                           <transaction_id>$trade_no</transaction_id>
                           <sign>$sign</sign>
                        </xml>";
            $result = Common::request('https://api.mch.weixin.qq.com/pay/orderquery', $data);

            Yii::warning('wx_check_result:' . $result);
            $check_xml = simplexml_load_string($result);
            if ((string)$check_xml->return_code[0] == 'SUCCESS' && (string)$check_xml->result_code[0] == 'SUCCESS') {
                $check_result = true;
            }
        }
        echo "<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                  </xml>";
        if ($check_result) {
            return [$out_trade_no, $trade_no, $vender_str];
        } else {
            return false;
        }
    }

    /*
     * 公众号推送
     */
    public function push($template, $open_id, $param, $target_url)
    {
        $token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token;

        $params = [];
        foreach ($param as $key => $value) {
            $params[$key] = ['value' => $value];
        }

        $data = [
            'touser' => $open_id,
            'template_id' => $template,
            'url' => $target_url,
            'data' => $params,
        ];
        return Common::request($url, json_encode($data));
    }

    /*
     * 网页方式获取用户信息
     */
    public function getInfo($open_id)
    {
        if (!$this->sns_token) {
            return false;
        }
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$this->sns_token&openid=$open_id&lang=zh_CN";
        $info = Common::request($url);
        return json_decode($info, true);
    }

    /*
     * 接口方式获取用户信息
     */
    public function getInfoFromServer($open_id)
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=$open_id&lang=zh_CN";
        $info = Common::request($url);
        return json_decode($info, true);
    }

    /*
     * 检查用户是否关注公众号
     */
    public function checkSub($open_id)
    {
        if ($this->_isSub === null) {
            $info = $this->getInfoFromServer($open_id);
            Yii::warning('ddd:' . json_encode($info));
            if (isset($info['subscribe']) && $info['subscribe'] == 1) {
                $this->_isSub = true;
            } else {
                $this->_isSub = false;
            }
        }
        return $this->_isSub;
    }

    /*
     * 获取微信保存的媒体文件
     */
    public function getFile($media_id)
    {
        $token = $this->getAccessToken();
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$token&media_id=$media_id";
        if ($data = Common::request($url)) {
            $file_name = Yii::getAlias("@app/answer/" . Common::getOrderId() . ".amr");
            file_put_contents($file_name, $data);
            return $file_name;
        }
        return false;
    }

    /*
     * 企业向用户付款
     */
    public function payToUser($open_id, $money, $ip)
    {
        $pay_id = Common::getOrderId();
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $param = [
            'mch_appid' => $this->appId,
            'mchid' => $this->appMchId,
            'nonce_str' => $this->getNonceStr(),
            'partner_trade_no' => $pay_id,
            'openid' => $open_id,
            'check_name' => 'NO_CHECK',
            'amount' => $money * $this->test_rate,
            'desc' => '『大咖说』的『问咖』结算',
            'spbill_create_ip' => $ip,
        ];
        $param['sign'] = $this->makeSign($param);
        $xml = Common::makeXML($param);
        Yii::warning('wxpay_touser_from:' . $xml);
        $data = Common::request($url, $xml, [], true, ['cert' => Yii::getAlias($this->certFile), 'key' => Yii::getAlias($this->certKey)]);
        Yii::warning('wxpay_touser_back:' . $data);
        $result = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($result['result_code'] != 'SUCCESS') {
            Yii::warning('wxpay_touser_fail');
            return false;
        }
        return $pay_id;
    }

    /*
     * 退款
     */
    public function refund($order_id, $money)
    {
        $refund_id = Common::getOrderId();
        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        $param = [
            'appid' => $this->appId,
            'mch_id' => $this->appMchId,
            'nonce_str' => $this->getNonceStr(),
            'out_trade_no' => $order_id,
            'out_refund_no' => $refund_id,
            'total_fee' => $money * $this->test_rate,
            'refund_fee' => $money * $this->test_rate,
            'op_user_id' => $this->appMchId,
        ];
        $param['sign'] = $this->makeSign($param);
        $xml = Common::makeXML($param);
        Yii::warning('wxpay_refund_from:' . $xml);
        $data = Common::request($url, $xml, [], true, ['cert' => Yii::getAlias($this->certFile), 'key' => Yii::getAlias($this->certKey)]);
        Yii::warning('wxpay_refund_back:' . $data);
        $result = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($result['return_code'] != 'SUCCESS') {
            Yii::warning('wxpay_refund_fail');
            return false;
        }
        return $refund_id;
    }

    /*
     * 判断请求是否发自微信
     */
    public function getIsWeixin()
    {
        if ($this->_isWeixin === null) {
            $str = strstr(strtolower(Yii::$app->request->userAgent), 'micromessenger');
            $this->_isWeixin = !empty($str);
        }
        return $this->_isWeixin;
    }

    public function getWebToken($type = 'base')
    {
        if ($code = Yii::$app->request->get('code', '')) {
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appId .
                '&secret=' . $this->appSecret . "&code=" . $code . "&grant_type=authorization_code";
            $r = Common::request($url);
            $data = json_decode($r, true);
            if (isset($data['openid'])) {
                if (
                    ($data['scope'] == 'snsapi_base' && $type == 'base')
                    || ($data['scope'] == 'snsapi_userinfo' && $type == 'user')
                ) {
                    $this->openId = $data['openid'];
                    $this->sns_token = $data['access_token'];
                    return self::OK;
                }
            }
        }

        if ($type == 'base') {
            $scope = 'snsapi_base';
        } else {
            $scope = 'snsapi_userinfo';
        }
        $this->redirect_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->appId .
            "&redirect_uri=" . urlencode(Yii::$app->request->absoluteUrl) .
            "&response_type=code&scope=$scope&state=STATE#wechat_redirect";
        return self::NEED_REDIRECT;
    }
}