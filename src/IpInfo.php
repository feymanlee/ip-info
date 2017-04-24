<?php

namespace IpInfo;


use IpInfo\Exceptions\GetIpIpInfoFailedException;
use IpInfo\Exceptions\IpIllegalException;
use IpInfo\Exceptions\MethodNotExistException;

/**
 * Class IpInfo
 * @package IpInfo
 */
class IpInfo
{
    /**
     * @var
     */
    private $ip;
    /**
     * @var
     */
    private $info = [
        'ip'      => '',
        'country' => '',
        'area'    => '',
        'region'  => '',
        'city'    => '',
        'county'  => '',
        'isp'     => '',
    ];
    /**
     * 请求淘宝 IP 库 api 超时时间
     * @var int
     */
    private $outTime = 1;

    /**
     * IpInfo constructor.
     *
     * @param $ip
     *
     * @throws IpIllegalException
     */
    public function __construct($ip)
    {
        $this->ip = $ip;
        $this->query();
    }

    /**
     * @param string $delimiter
     * @param bool   $full
     *
     * @return string
     */
    public function address($delimiter = ' ', $full = false)
    {
        $struct = [
            $this->info['country'],
            $this->info['area'],
            $this->info['region'],
            $this->info['city'],
            $this->info['county'],
        ];
        if (!$full) {
            array_splice($struct, 1, 1);
        }
        $struct = array_filter($struct);

        return implode($delimiter, $struct);
    }

    /**
     * @return array ip 的信息
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws MethodNotExistException
     */
    public function __call($name, $arguments)
    {

        if (isset($this->info, $name)) {
            return $this->info[$this->snakeCase($name)];
        } else {
            throw new MethodNotExistException('Call a Not Exist Method:' . $name);
        }
    }

    /**
     * @return mixed
     */
    public function getOutTime()
    {
        return $this->outTime;
    }

    /**
     * @param mixed $outTime
     *
     * @return $this
     */
    public function setOutTime($outTime)
    {
        $this->outTime = $outTime;

        return $this;
    }

    /**
     * @throws GetIpIpInfoFailedException
     */
    private function query()
    {
        $this->info['ip'] = $this->ip;
        if ($this->ip === '127.0.0.1') {
            $this->info['country'] = '本机';
        } elseif ($this->isInternal()) {
            $this->info['country'] = 'INNA 保留地址';
        } else {
            // 先通过 api 获取，api 获取失败的话通过本地数据库获取
            $result = $this->apiQuery();
            if ($result === false) {
                // api 获取失败的话进行本地获取
                $result = $this->localQuery();
            }

            $this->info = $result;
        }
    }

    private function apiQuery()
    {
        return (new IpTaobaoApiQuery)->setOutTime($this->outTime)->query($this->ip);
    }

    private function localQuery()
    {
        return IpLocalQuery::create()->query($this->ip);
    }

    private function isInternal()
    {
        $ipLong   = ip2long($this->ip);
        $netLocal = ip2long('127.255.255.255') >> 24; //127.x.x.x
        $netA     = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
        $netB     = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
        $netC     = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址

        return $ipLong >> 24 === $netLocal || $ipLong >> 24 === $netA || $ipLong >> 20 === $netB || $ipLong >> 16 === $netC;
    }

    /**
     * @param        $value
     * @param string $delimiter
     *
     * @return mixed|string
     */
    private function snakeCase($value, $delimiter = '_')
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', $value);
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return $value;
    }
}