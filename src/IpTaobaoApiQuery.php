<?php

namespace IpInfo;

class IpTaobaoApiQuery extends IpQueryContract
{

    /**
     * 淘宝 IP 库 URL
     */
    const URI = 'http://ip.taobao.com/service/getIpInfo.php?ip=';
    /**
     * @var int 超时时间
     */
    private $outTime = 1;


    public function query($ip)
    {
        $this->ip = $ip;
        $result   = $this->doGet(self::URI . $this->ip);
        if ($result !== false) {
            $data = json_decode($result, 1);
            if ($data['code'] !== 1) {
                return $data['data'];
            }
        }

        return false;
    }

    /**
     * 设置超时时间，单位：s
     *
     * @param int $outTime
     *
     * @return $this
     */
    public function setOutTime($outTime)
    {
        $this->outTime = $outTime;

        return $this;
    }

    private function doGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->outTime * 1000);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)",
        ]);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}