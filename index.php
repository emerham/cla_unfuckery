<?php

require 'vendor/autoload.php';

use League\Csv\Writer;

const CLA_USERS_ALIAS_FILE = __DIR__ . '/cla_users_alias';
const CLA_PEOPLE_ALIAS_FILE = __DIR__ . '/cla_people_alias';
const CLA_DIRECTORY_PATHS_FILE = __DIR__ . '/cla_directory_paths';
const REDIRECTS_FILE = 'redirects.csv';
const CSV_HEADERS = ['Path', 'Redirect', 'Code'];

/**
 * Extracts user data from a given file path.
 */
function extractUserData(string $filePath): array {
  $content = file_get_contents($filePath);
  $rows = explode("\n", $content);
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

  return $data;
}

/**
 * Reads directory paths and constructs a lookup table.
 */
function createLookupTable(string $filePath): array {
  $content = file_get_contents($filePath);
  $rows = explode("\n", $content);
  $lookup = [];

  foreach ($rows as $row) {
    if (empty($row)) {
      continue;
    }
    [$target, $alias] = explode("\t", $row);
    $lookup[$alias] = $target;
  }

  return $lookup;
}

$data = array_merge_recursive(extractUserData(CLA_USERS_ALIAS_FILE), extractUserData(CLA_PEOPLE_ALIAS_FILE));
$lookup = createLookupTable(CLA_DIRECTORY_PATHS_FILE);
$redirects = [];

// Process redirects
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

// Write results to CSV
$csv = Writer::createFromPath(REDIRECTS_FILE, 'w+');
$csv->insertOne(CSV_HEADERS);
$csv->insertAll($redirects);

print("Created" . PHP_EOL);
