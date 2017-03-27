<?php

require_once 'vendor/autoload.php';

$files = new Illuminate\Filesystem\Filesystem();
$tracker = new JasonLewis\ResourceWatcher\Tracker();

$watcher = new JasonLewis\ResourceWatcher\Watcher($tracker, $files);

$config = json_decode('multiwatch.json');

/*
$listener = $watcher->watch('test');
$listener->onAnything(function ($event, $resource, $path)
{
	echo $path . PHP_EOL;
});

$listener2 = $watcher->watch('test2');
$listener2->onAnything(function ($event, $resource, $path)
{
	echo '222' . $path . PHP_EOL;
});

$watcher->start();
*/

echo "test";