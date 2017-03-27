<?php

require_once 'vendor/autoload.php';

$files = new Illuminate\Filesystem\Filesystem();
$tracker = new JasonLewis\ResourceWatcher\Tracker();

$watcher = new JasonLewis\ResourceWatcher\Watcher($tracker, $files);

if (!file_exists('multiwatch.json'))
{
	die('multiwatch.json not found');
}

$config = json_decode(file_get_contents('multiwatch.json'), true);
foreach ($config['watcher'] as $pattern => $command)
{
	$path = dirname($pattern);
	$filePattern = basename($pattern);
	$fileData = explode('.', $filePattern);
	$fileExtension = end($fileData);

	$listener = $watcher->watch($path);
	$listener->onAnything(function ($event, $resource, $resourceList) use ($fileExtension, $command)
	{
		foreach ($resourceList as $resourceExtension => $resourceFiles)
		{
			if ($resourceExtension == $fileExtension)
			{
				$cmd = str_replace('{{files}}', implode(' ', $resourceFiles), $command);
				echo exec($cmd);
			}
		}
	});
}

$watcher->start();