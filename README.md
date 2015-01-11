# Type Police

Enforce super type contract of an object

## Usage

```php
use InterNations\Component\TypePolice\Factory\PolicedProxyFactory;

$file = new SplFileObject(__FILE__);

$factory = new PolicedProxyFactory();
$file = $factory->policeInstance($file, 'SplFileInfo');
```
