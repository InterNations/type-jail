# Type Jail

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/InterNations/type-jail?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![Build Status](https://travis-ci.org/InterNations/type-jail.svg?branch=master)](https://travis-ci.org/InterNations/type-jail) [![Dependency Status](https://www.versioneye.com/user/projects/54b62fa1050646e16d0000cb/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54b62fa1050646e16d0000cb) [![Average time to resolve an issue](https://isitmaintained.com/badge/resolution/InterNations/type-jail.svg)](https://isitmaintained.com/project/InterNations/type-jail "Average time to resolve an issue") [![Percentage of issues still open](https://isitmaintained.com/badge/open/InterNations/type-jail.svg)](https://isitmaintained.com/project/InterNations/type-jail "Percentage of issues still open")

Enforce super type contract of an object

## Usage

```php
use InterNations\Component\TypeJail\Factory\SuperTypeFactory;
use InterNations\Component\TypeJail\Factory\JailFactory;
use InterNations\Component\TypeJail\Factory\SuperTypeJailFactory;

$file = new SplFileObject(__FILE__);


$factory = new JailFactory();
$file = $factory->createInstanceJail($file, 'SplFileInfo');

// Will return true
var_dump($file instanceof SplFileInfo);

// Will return the file path because that method is declared in SplFileInfo
$file->getFilePath();

// Will throw an exception indicating a type violation because that method is declared in SplFileObject
$file->flock();


$factory = new SuperTypeJailFactory();
$file = $factory->createInstanceJail($file, 'SplFileInfo');

// Will return false
var_dump($file instanceof SplFileInfo);

// Will return the file path because that method is declared in SplFileInfo
$file->getFilePath();

// Will throw an exception indicating a type violation because that method is declared in SplFileObject
$file->flock();


$factory = new SuperTypeFactory();
$file = $factory->createInstanceJail($file, 'SplFileInfo');

// Will return false
var_dump($file instanceof SplFileInfo);

// Will return the file path because that method is declared in SplFileInfo
$file->getFilePath();

// Fatal error: method not found
$file->flock();
```

## Acknowledgement
Standing on the shoulders of [ocramius/proxy-manager](https://github.com/Ocramius/ProxyManager/) by Marco Pivetta that makes it super-duper easy to work with proxies.
