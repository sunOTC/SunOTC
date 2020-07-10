# SunOTC-open-sdk-php
>基于PHP的OTC商户api接口SDK

-------
### 安装方法

```
$ composer require sun-otc/sun-otc-open 
```

### 使用方法

```
use SunOTC\SunOTC;

    $data       = '{"app_id":"123","merc_order_id":"200611184930170114"}';
    $OtcService = (new SunOTC('', Dictionary::PRI_KEY));
    $OtcService->getOrderMsg($data);
    //                //查询交易限制
    $data       = '{"app_id":"123"}';
    $OtcService = (new SunOTC('', Dictionary::PRI_KEY));
    $OtcService->getTradeLimit($data);*/
    
    //查询商户资产
     $data = '{"app_id":"123","merc_order_id":"200611184930170114"}';
     $OtcService = (new SunOTC('',Dictionary::PRI_KEY));
     $OtcService->getMercAsset($data);
    
    //查询价格
    $data       = '{"app_id":"123","coin":"USDT"}';
    $OtcService = (new SunOTC('', Dictionary::PRI_KEY));
    $OtcService->getTradePrice($data);
       
```

### 链接
* https://docs.sunotc.com




