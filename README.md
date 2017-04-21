# ip-info
通过 IP 得到对应的国家 、省（自治区或直辖市）、市（县）、运营商。基于 [淘宝 IP 库](http://ip.taobao.com/instructions.php) 。
## Install
```shell
composer require feyman/ip-info
```
## Usage
```php
//实例化
$info  = new IpInfo($ip);
//获取详细信息
$info->info();
//获取地址
// $delimiter 地址各段之间的分隔符
// $full 是否获取完整的地址，默认获取的地址显示为 中国 北京 北京 朝阳 国贸，
// $full = true 时，或加上 area 显示为 中国 华北 北京 北京 朝阳 国贸
$info->address($delimiter = ' ', $full = false);
// 获取 ISP 信息
$info->isp();
// 获取 国家 信息
$info->country();
// 获取 地区 信息，华东，华南，华北
$info->area();
// 获取 省/行政区 信息
$info->region();
// 获取 州/市 信息
$info->city();
// 获取 区/县 信息
$info->county();
```
## License
MIT
