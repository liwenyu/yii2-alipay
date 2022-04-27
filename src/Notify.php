<?php

namespace srun\alipay;

use yii\helpers\ArrayHelper;

/* *
 * 类名：AlipaySubmit
 * 功能：支付宝各接口请求提交类
 * 详细：构造支付宝各接口表单HTML文本，获取远程HTTP数据
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */

class Notify
{
    /**
     * HTTPS形式消息验证地址
     */
    public $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * HTTP形式消息验证地址
     */
    public $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    public $config = [];

    function __construct()
    {
        //默认设置
        $_config = require(__DIR__ . '/config.php');
        //load config file
        $this->config = ArrayHelper::merge($_config, $this->config);
    }

    function Notify()
    {
        $this->__construct();
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    public function verifyNotify()
    {
        //判断POST来的数组是否为空
        if (empty($_POST)) {
            return false;
        } else {
            //生成签名结果
            $mysign = $this->getMysign($_POST);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (!empty($_POST["notify_id"])) {
                $responseTxt = $this->getResponse($_POST["notify_id"]);
            }
            //写日志记录
            $_POST['responseTxt'] = $responseTxt;
            $core = new Core();
            $core->logResult($_POST);
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $mysign == $_POST["sign"]) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    protected function verifyReturn()
    {
        if (empty($_GET)) {//判断GET来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $mysign = $this->getMysign($_GET);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (!empty($_GET["notify_id"])) {
                $responseTxt = $this->getResponse($_GET["notify_id"]);
            }
            //写日志记录
            $log_text = "responseTxt=" . $responseTxt . "\n notify_url_log:sign=" . $_GET["sign"] . "&mysign=" . $mysign . ",";
            $core = new Core();
            $log_text = $log_text . $core->createLinkString($_GET);
            $core->logResult($log_text);
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $mysign == $_GET["sign"]) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 根据反馈回来的信息，生成签名结果
     * @param $para_temp 通知返回来的参数数组
     * @return 生成的签名结果
     */
    protected function getMysign($para_temp)
    {
        $core = new Core();
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $core->paraFilter($para_temp);
        //对待签名参数数组排序
        $para_sort = $core->argSort($para_filter);
        //生成签名结果
        $mysign = $core->buildMysign($para_sort, trim($this->config['key']), strtoupper(trim($this->config['sign_type'])));
        return $mysign;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     *                   验证结果集：
     *                   invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     *                   true 返回正确信息
     *                   false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    protected function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->config['transport']));
        $partner = trim($this->config['partner']);
        $veryfy_url = '';

        if ($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        } else {
            $veryfy_url = $this->http_verify_url;
        }

        $veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
        $core = new Core();
        $responseTxt = $core->getHttpResponse($veryfy_url);
        return $responseTxt;
    }
}