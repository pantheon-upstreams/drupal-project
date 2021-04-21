#!/usr/bin/env php -dmemory_limit=2048m
<?php

$help_text = <<<EOT
 *******************************************************************************
 * THIS SCRIPT IS IN ALPHA VERSION STATUS AND AT THIS POINT HAS VERY LITTLE    *
 * ERROR CHECKING. PLEASE USE AT YOUR OWN RISK.                                *
 *******************************************************************************
 * This script searches for every {modulename}.info.yml. If that file has a    *
 * "project" proerty (i.e. it's been thru the automated services at            *
 * drupal.org), it records that property and version number and ensures        *
 * those values are in the composer.json "require" array. Your old composer    *
 * file will re renamed backup-*-composer.json.                                *
 *******************************************************************************
EOT;

function progressBar($done, $total) {
    $perc = floor(($done / $total) * 100);
    $left = 100 - $perc;
    $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
    fwrite(STDERR, $write);
}



echo $help_text . PHP_EOL;

$regex = '/(\.info\.yml|\.info\.yaml?)/';

$allFiles = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(getcwd() . "/OLDSITE")));

$max = count($allFiles);
$current = 0;
$infoFiles = array_filter($allFiles, function(SPLFileInfo $file) use ($regex, &$max, &$current) {
  progressBar($current++, $max);
  return preg_match($regex, $file->getFilename()) && !strpos($file->getFilename(), 'test');
});
$requirements = [];



foreach ($infoFiles as $fileName => $fileInfo) {
  $contents = file_get_contents($fileName);
  preg_match('/project\:\ ?\'(.*)\'$/m', $contents, $projectMatches);
  preg_match('/version\:\ ?\'(.*)\'$/m', $contents, $versionMatches);
  if (is_array($projectMatches) && isset($projectMatches[1])) {
    if ($projectMatches[1]) {
      $requirements[ "drupal/" . $projectMatches[1] ] = "^" . str_replace("8.x-", "", $versionMatches[1]);
    }
  }
}

$oldComposer = new SplFileInfo(getcwd(). "/composer.json");
if ($oldComposer instanceof SplFileInfo) {
  $newFilename = "backup-" . uniqid() . "-" . $oldComposer->getFilename();
  $contents = file_get_contents($oldComposer->getRealPath());
  if (!empty($contents)) {
    $composerFile = json_decode($contents, true);
    $requirements = array_merge($composerFile['require'], $requirements);
    ksort($requirements);
    $composerFile['require'] = $requirements;
    copy($oldComposer->getRealPath(),  $oldComposer->getPath() . "/" . $newFilename);
    file_put_contents($oldComposer->getRealPath(), json_encode($composerFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE, 5));
  }

}
