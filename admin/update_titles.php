<?php
<?php
/**
 * Recursively updates <title> tags in all PHP files,
 * adding "TUT | " before "Online Library Management".
 */

$directory = __DIR__ . '/../'; // Adjust path if needed

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if (strtolower($file->getExtension()) !== 'php') continue;

    $path = $file->getPathname();
    $contents = file_get_contents($path);

    // Regex to match the title tag containing "Online Library Management"
    $pattern = '/<title>TUT | \s*(?!TUT\s*\|)(.*?)Online Library Management(.*?)<\/title>/i';
    $replacement = '<title>TUT | TUT | $1Online Library Management$2</title>';

    $newContents = preg_replace($pattern, $replacement, $contents, -1, $count);

    if ($count > 0) {
        file_put_contents($path, $newContents);
        echo "Updated: $path\n";
    }
}
echo "Done.\n";
?>