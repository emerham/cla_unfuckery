<?php

require 'vendor/autoload.php';

use League\Csv\Writer;

$cla_users = file_get_contents(__DIR__ . '/cla_users_alias');
$rows = explode("\n", $cla_users);
$data = [];
foreach ($rows as $row) {
  if (empty($row)) {
    continue;
  }
  [$path, $alias] = explode("\t", $row);
  $alias = ltrim($alias, '/');
  $uid = explode('/', $path)[2];
  $data[$uid][] = $alias;
}
$cla_people = file_get_contents(__DIR__ . '/cla_people_alias');
$rows = explode("\n", $cla_people);
foreach ($rows as $row) {
  if (empty($row)) {
    continue;
  }
  [$path, $alias] = explode("\t", $row);
  $alias = ltrim($alias, '/');
  $uid = explode('/', $path)[2];
  $data[$uid][] = $alias;
}

$directory_paths = file_get_contents(__DIR__ . '/cla_directory_paths');
$rows = explode("\n", $directory_paths);
$lookup = [];
$redirects = [];

foreach ($rows as $row) {
  if (empty($row)) {
    continue;
  }
  [$target, $alias] = explode("\t", $row);
  $lookup[$alias] = $target;
}

foreach ($data as $uid => $aliases) {
  foreach ($aliases as $alias) {
    $name = explode('/', $alias)[1];
    foreach ($lookup as $directory => $node) {
      if (strpos($directory, $name) !== FALSE) {
        $redirects[] = [$alias, $node, 301];
      }
    }
  }
}
$csv = Writer::createFromPath('redirects.csv', 'w+');
$headers = ['Path', 'Redirect', 'Code'];
$csv->insertOne($headers);
$csv->insertAll($redirects);
print("Created" . PHP_EOL);
