<?php

use Composer\InstalledVersions;

require_once __DIR__ . '/../vendor/autoload.php';

if (!InstalledVersions::isInstalled('moodle/moodle')) {
    http_response_code(500);
    die("Moodle must be available in the composer vendor directory.");
}

$dirRoot = InstalledVersions::getInstallPath('moodle/moodle');
if (is_null($dirRoot)) {
    http_response_code(500);
    die("Unable to find Moodle path in composer vendor directory.");
}

$script = $_SERVER['SCRIPT_NAME'];
$phpPosition = strpos($script, '.php');
if ($phpPosition === false) {
    if (str_starts_with($script, '/pix')) {
        // Serve pix files directly.
        if (file_exists($dirRoot . $script)) {
            $contentType = mime_content_type($dirRoot . $script);
            header('Content-Type: ' . $contentType);
            readfile($dirRoot . $script);
            exit;
        }
    } elseif (str_ends_with($script, '/')) {
        // If the script ends with a slash, we assume it's a directory and we try to load index.php
        $script .= 'index.php';
        $phpPosition = strpos($script, '.php');
    } else {
        http_response_code(404);
        die;
    }


}

$phpScript = substr($script, 0, $phpPosition + 4);

$scriptPath = $dirRoot . '/' . ltrim($phpScript, '/');

$pathInfo = substr($script, $phpPosition + 4);

if (!empty($pathInfo)) {
    $_SERVER['PATH_INFO'] = $pathInfo;
}

if (!file_exists($scriptPath)) {
    http_response_code(404);
    die;
}

chdir(dirname($scriptPath));
require_once $scriptPath;