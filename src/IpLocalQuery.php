<?php

namespace IpInfo;

use IpInfo\Exceptions\DatabaseNotExistException;
use IpInfo\Exceptions\InvalidDatabaseFileException;
use IpInfo\Exceptions\IpIllegalException;

class IpLocalQuery extends IpQueryContract
{
    private static $instance = NULL;

    public $encoding = 'UTF-8';

    protected $file;
    private   $offset;
    private   $fp;
    private   $index;

    /**
     * 静态工厂方法，返还此类的唯一实例
     */
    public static function create()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 防止用户克隆实例
     */
    public function __clone()
    {
        die('Clone is not allowed.' . E_USER_ERROR);
    }

    private function __construct()
    {
        $this->openDataBase(realpath('data/ip_data.dat'));
    }

    public function __destruct()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
    }

    public function query($ip)
    {
        $this->ip = $ip;
        if (!$this->ipCheck()) {
            throw new IpIllegalException('Illegal IP:' . $this->ip);
        }
        $nip   = gethostbyname($this->ip);
        $ipdot = explode('.', $nip);

        $nip2 = pack('N', ip2long($nip));

        $tmp_offset = (int)$ipdot[0] * 4;
        $start      = unpack('Vlen', $this->index[$tmp_offset] . $this->index[$tmp_offset + 1] . $this->index[$tmp_offset + 2] . $this->index[$tmp_offset + 3]);

        $index_offset = $index_length = NULL;
        $max_comp_len = $this->offset['len'] - 1024 - 4;
        for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
            if ($this->index{$start} . $this->index{$start + 1} . $this->index{$start + 2} . $this->index{$start + 3} >= $nip2) {
                $index_offset = unpack('Vlen', $this->index{$start + 4} . $this->index{$start + 5} . $this->index{$start + 6} . "\x0");
                $index_length = unpack('Clen', $this->index{$start + 7});

                break;
            }
        }

        if ($index_offset === NULL) {
            return 'N/A';
        }

        fseek($this->fp, $this->offset['len'] + $index_offset['len'] - 1024);

        $data = explode("\t", fread($this->fp, $index_length['len']));

        return [
            'ip'      => $this->ip,
            'country' => array_get($data, 0, ''),
            'area'    => '',
            'region'  => array_get($data, 0, '') !== array_get($data, 1, '') ? array_get($data, 1, '') : '',
            'city'    => array_get($data, 2, ''),
            'county'  => array_get($data, 3, ''),
            'isp'     => '',
        ];

    }

    private function openDataBase($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new DatabaseNotExistException($file . ' does not exist, or is not readable');
        }
        $this->file   = $file;
        $this->fp     = fopen($file, 'rb');
        $this->offset = unpack('Nlen', fread($this->fp, 4));
        if ($this->offset['len'] < 4) {
            throw new InvalidDatabaseFileException('Invalid Database File!');
        }
        $this->index = fread($this->fp, $this->offset['len'] - 4);
    }
}