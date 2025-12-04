<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// --- GARBAGE COLLECTION (Auto-Delete Old Files) ---
// This deletes files in 'downloads' older than 10 minutes to save space
$files = glob(__DIR__ . '/downloads/*');
$now   = time();
if ($files) {
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 600) { // 600 seconds = 10 minutes
                unlink($file);
            }
        }
    }
}
// --------------------------------------------------

$url = isset($_GET['url']) ? $_GET['url'] : '';

if (!$url) {
    echo json_encode(["status" => "error", "message" => "No URL provided"]);
    exit;
}

// Run Python to download and merge
$command = "python3 downloader.py " . escapeshellarg($url) . " 2>&1";
$output = shell_exec($command);

if ($output) {
    $json = json_decode($output, true);
    
    if (isset($json['status']) && $json['status'] === 'success') {
        // Construct the full download URL
        // It detects your Coolify domain automatically
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $domain = $_SERVER['HTTP_HOST'];
        $file_url = "$protocol://$domain/downloads/" . $json['filename'];

        echo json_encode([
            "status" => "success",
            "title" => $json['title'],
            "download_link" => $file_url, // This is the merged MP4 link
            "note" => "Link expires in 10 minutes"
        ]);
    } else {
        // Pass the python error to the user
        echo $output;
    }
} else {
    echo json_encode(["status" => "error", "message" => "Server execution failed"]);
}
?>
