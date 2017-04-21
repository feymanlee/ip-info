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
     *
     */
    const URI = 'http://ip.taobao.com/service/getIpInfo.php?ip=';
    /**
     * @var
     */
    private $ip;
    /**
     * @var
     */
    private $info;

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
        if (!$this->ipCheck()) {
            throw new IpIllegalException('Illegal IP:' . $this->ip);
        }
        $this->resolve();
    }

    /**
     * @param string $delimiter
     * @param bool   $full
     *
     * @return string
     */
    public function address($delimiter = ' ', $full = false)
    {
        if ($this->ip === '127.0.0.1') {
            return '本机';
        }
        if ($this->isInternal()) {
            return 'INNA 保留地址';
        }
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
     * @throws GetIpIpInfoFailedException
     */
    private function resolve()
    {
        $result = file_get_contents(self::URI . $this->ip);
        $data   = json_decode($result, 1);
        if ($result === false || $data['code'] === 1) {
            throw new GetIpIpInfoFailedException('Failed to Get IP Info，Please Try Again');
        } else {
            $this->info = $data['data'];
        }
    }

    /**
     * @return int
     */
    private function ipCheck()
    {
        return preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/u', $this->ip);
    }

    public function isInternal()
    {
        $netLocal = ip2long('127.255.255.255') >> 24; //127.x.x.x
        $netA     = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
        $netB     = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
        $netC     = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址

        return $this->ip >> 24 === $netLocal || $this->ip >> 24 === $netA || $this->ip >> 20 === $netB || $this->ip >> 16 === $netC;
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