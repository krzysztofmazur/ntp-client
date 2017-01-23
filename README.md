# ntp-client

Ntp-client is a PHP library to getting time from NTP server. 
It supports UPD and TCP protocol.

## Installation

Run composer command

```composer require krzysztofmazur/ntp-client```

## Usage

```
<?php

use KrzysztofMazur\NTPClient\Impl\UdpNtpClient;

$client = new UdpNtpClient('pool.ntp.org', 123);
var_dump($client->getUnixTime());

```

or

```
<?php

use KrzysztofMazur\NTPClient\Impl\UdpNtpClient;
use KrzysztofMazur\NTPClient\Impl\CompositeNtpClient;

$clients = [
    new UdpNtpClient('pool.ntp.org', 123),
    new UdpNtpClient('ntp.pads.ufrj.br', 123)
];
$client = new CompositeNtpClient($clients);

var_dump($client->getTime(new DateTimeZone('Europe/Warsaw')));
```

## License

MIT