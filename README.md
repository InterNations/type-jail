# Type Jail [![Test](https://github.com/InterNations/type-jail/actions/workflows/tests.yaml/badge.svg)](https://github.com/InterNations/type-jail/actions/workflows/tests.yaml)
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

// Will throw an exception indicating a type violation because that method
// is declared in SplFileObject
$file->flock();


$factory = new SuperTypeJailFactory();
$file = $factory->createInstanceJail($file, 'SplFileInfo');

// Will return false
var_dump($file instanceof SplFileInfo);

// Will return the file path because that method is declared in SplFileInfo
$file->getFilePath();

// Will throw an exception indicating a type violation because that method
// is declared in SplFileObject
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
