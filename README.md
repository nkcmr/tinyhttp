### tinyhttp

a small http utility in php. i use this for a few scripts, its handy.

```php
<?php

use \tinyhttp\tinyhttp as http;

$ip = http::get('https://icanhazip.com');
var_dump($ip); // 0.0.0.0

```

#### todo
- [ ] gzip
- [ ] deflate
- [ ] HTTP DELETE
- [ ] HTTP POST
- [ ] HTTP HEAD
- [ ] redirects