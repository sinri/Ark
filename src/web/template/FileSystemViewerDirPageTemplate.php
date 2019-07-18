<?php

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset='UTF-8'>
    <title>
        ~/
        <?php
        /** @noinspection PhpUndefinedVariableInspection */
        echo implode(" / ", $components);
        ?>
        - Ark File System Viewer</title>
    <style>
        table th, td {
            padding: 5px 10px;
        }
    </style>
</head>
<body>
<h1>Ark File System Viewer</h1>
<p>You are here: ~/<?php echo implode("/", $components); ?></p>
<hr>
<table>
    <tr>
        <th>Name</th>
        <th>Size</th>
        <th>Created</th>
        <th>Modified</th>
        <th>Accessed</th>
    </tr>
    <?php
    /** @noinspection PhpUndefinedVariableInspection */
    $dir = opendir($realPath);
    while ($item = readdir($dir)) {
        if ($item === '.') continue;
        if ($item === '..' && empty($components)) {
            continue;
        }

        $fileStat = stat($realPath . '/' . $item);

        $fileSize = $fileStat[7];//filesize($realPath.'/'.$item);
        $lastAccessTime = date("Y-m-d H:i:s", $fileStat[8]);
        $lastModificationTime = date("Y-m-d H:i:s", $fileStat[9]);
        $lastCreateTime = date("Y-m-d H:i:s", $fileStat[10]);

        $fileSize = number_format($fileSize);

        echo "<tr>"
            . "<td>" . "<a href='./{$item}'>{$item}</a> " . "</td>"
            . "<td>{$fileSize} bytes</td>"
            . "<td>{$lastCreateTime}</td>"
            . "<td>{$lastModificationTime}</td>"
            . "<td>{$lastAccessTime}</td>"
            . "</tr>" . PHP_EOL;
    }
    closedir($dir);
    ?>
</table>
<hr>
<div>
    This page is generated on <?php echo date('Y-m-d H:i:s') . " Timezone " . date_default_timezone_get(); ?>,
    powered by Framework <a href='https://github.com/sinri/Ark' target='_blank'>sinri/ark</a>.
</div>
</body>
</html>