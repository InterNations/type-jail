# Type Police

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/InterNations/type-police?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![Build Status](https://travis-ci.org/InterNations/type-police.svg?branch=master)](https://travis-ci.org/InterNations/type-police) [![Dependency Status](https://www.versioneye.com/user/projects/54b2a9c12eea784acc000323/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54b2a9c12eea784acc000323) [![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/InterNations/type-police.svg)](http://isitmaintained.com/project/InterNations/type-police "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/InterNations/type-police.svg)](http://isitmaintained.com/project/InterNations/type-police "Percentage of issues still open")

Enforce super type contract of an object

## Usage

```php
use InterNations\Component\TypePolice\Factory\SuperProxyFactory;
use InterNations\Component\TypePolice\Factory\PolicedProxyFactory;
use InterNations\Component\TypePolice\Factory\PolicedSuperProxyFactory;

$file = new SplFileObject(__FILE__);


$factory = new PolicedProxyFactory();
$file = $factory->policeInstance($file, 'SplFileInfo');

// Will return true
var_dump($file instanceof SplFileInfo);

// Will return the file path
$file->getFilePath();

// Will throw an exception indicating a type violation
$file->flock();


$factory = new PolicedSuperProxyFactory();
$file = $factory->policeInstance($file, 'SplFileInfo');

// Will return false
var_dump($file instanceof SplFileInfo);

// Will return the file path
$file->getFilePath();

// Will throw an exception indicating a type violation
$file->flock();


$factory = new SuperProxyFactory();
$file = $factory->policeInstance($file, 'SplFileInfo');

// Will return false
var_dump($file instanceof SplFileInfo);

// Will return the file path
$file->getFilePath();

// Will yield a method not found fatal error
$file->flock();
```

## Acknowledgement
Standing on the shoulders of [ocramius/proxy-manager](https://github.com/Ocramius/ProxyManager/) by Marco Pivetta that makes it super-duper easy to work with proxies.
