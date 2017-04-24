<?php

namespace IpInfo\Tests;

use IpInfo\IpInfo;

class IpInfoTest extends \PHPUnit_Framework_TestCase
{
    protected $ipInfo;

    protected function setUp()
    {
        $faker        = \Faker\Factory::create();
        $ip           = $faker->ipv4;
        $this->ipInfo = new IpInfo($ip);
        echo 'IP：' . $ip . ' ' . $this->ipInfo->address() . PHP_EOL;
    }

    public function testAddress()
    {
        $address = $this->ipInfo->address();
        $this->assertTrue(is_string($address));
    }

    public function testIp()
    {
        $ip = $this->ipInfo->ip();
        $this->assertRegExp('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/u', $ip);
    }

    public function testInfo()
    {
        $info = $this->ipInfo->info();

        $this->assertArrayHasKey('ip', $info);
        $this->assertArrayHasKey('country', $info);
        $this->assertArrayHasKey('area', $info);
        $this->assertArrayHasKey('region', $info);
        $this->assertArrayHasKey('city', $info);
        $this->assertArrayHasKey('county', $info);
        $this->assertArrayHasKey('isp', $info);
    }

    public function testIsp()
    {
        $this->assertTrue(is_string($this->ipInfo->isp()));
    }

    public function testCountry()
    {
        $this->assertTrue(is_string($this->ipInfo->country()));
    }

    public function testArea()
    {
        $this->assertTrue(is_string($this->ipInfo->area()));
    }

    public function testRegion()
    {
        $this->assertTrue(is_string($this->ipInfo->region()));
    }

    public function testCity()
    {
        $this->assertTrue(is_string($this->ipInfo->city()));
    }

    public function testCounty()
    {
        $this->assertTrue(is_string($this->ipInfo->county()));
    }
}
