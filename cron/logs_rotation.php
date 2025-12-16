<?php
// Get current working directory
$workDir = getcwd();

// Set logs and archive directories
$logDir = $workDir . '/logs';
$archiveDir = $logDir . '/archive';

// Create archive directory if it doesn't exist
if (!is_dir($archiveDir)) {
    mkdir($archiveDir, 0777, true);
}

// Get all files in logs directory
$files = scandir($logDir);

foreach ($files as $file) {
    $filePath = $logDir . '/' . $file;

    // Skip directories and the archive folder
    if ($file === '.' || $file === '..' || $file === 'archive' || is_dir($filePath)) {
        continue;
    }

    // Use file modification date for naming
    $newName = date('dmy', filemtime($filePath)) . '-' . $file;

    // Move file to archive directory
    rename($filePath, $archiveDir . '/' . $newName);
}

echo "Log rotation completed.\n";
