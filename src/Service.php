<?php

namespace srun\alipay;

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

class Service
{
    public $config;
    /**
     *支付宝网关地址（新）
     */
    public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

    public function __construct()
    {
        //默认设置
        $this->config = require(__DIR__ . '/config.php');
    }

    public function Service()
    {
        $this->__construct();
    }

    /**
     * 构造即时到帐接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息
     */
    public function create_direct_pay_by_user($para_temp)
    {
        //设置按钮名称
        $button_name = "确认";
        //生成表单提交HTML文本信息
        $submit = new Submit();
        $html_text = $submit->buildForm($para_temp, $this->alipay_gateway_new, "get", $button_name, $this->config);

        return $html_text;
    }

    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
     * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
     */
    public function query_timestamp()
    {
        $url = $this->alipay_gateway_new . "service=query_timestamp&partner=" . trim($this->config['partner']);
        $encrypt_key = "";

        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

        return $encrypt_key;
    }

    /**
     * 构造支付宝其他接口
     * @param $para_temp 请求参数数组
     * @return 表单提交HTML信息/支付宝返回XML处理结果
     */
    public function alipay_interface($para_temp)
    {
        //获取远程数据/生成表单提交HTML文本信息
        $submit = new Submit();
        $html_text = "";
        //请根据不同的接口特性，选择一种请求方式
        //1.构造表单提交HTML数据:（$method可赋值为get或post）
        //$alipaySubmit->buildForm($para_temp, $this->alipay_gateway_new, "get", $button_name, $this->aliapy_config);
        //2.构造模拟远程HTTP的POST请求，获取支付宝的返回XML处理结果:
        //注意：若要使用远程HTTP获取数据，必须开通SSL服务，该服务请找到php.ini配置文件设置开启，建议与您的网络管理员联系解决。
        //$alipaySubmit->sendPostInfo($para_temp, $this->alipay_gateway_new, $this->aliapy_config);

        return $html_text;
    }

    //获取配置
    public function getConfig()
    {
        return $this->config;
    }
}