<?php


namespace IpInfo;


abstract class IpQueryContract
{
    protected $ip;

    abstract public function query($ip);

    /**
     * @return int
     */
    protected function ipCheck()
    {
        return preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/u', $this->ip);
    }
}