CronCreator
===========

CronCreator is a simple library for the creation of cronjobs.

#### Adding Cronjobs via array
```php
$cronCreator = new \CronCreator\CronCreator();
$cronCreator->add(array(
    'every' => array(
        'unit'   => 'minute',
        'amount' => 2 
    ),
    'at' => array(10, 11, 3),
    'execute' => 'rawr'
));
$cronCreator->save();
```

#### Adding Cronjobs via Creator
```php
$cronCreator = new \CronCreator\CronCreator();
$cronjob = new \CronCreator\Creator();
$cronjob->at(array(10, 11, 3))
        ->every(2, 'minute')
        ->execute('rawr');
$cronCreator->add($cronjob);
$cronCreator->save();
```

### Installing via Composer

The recommended way to install Guzzle is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of CronCreator:

```bash
composer require mrferos/cron-creator
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```