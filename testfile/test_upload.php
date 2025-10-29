<?php
$path = 'D:/Uploads/hello.txt';

// if (!is_dir($path)) {
//     mkdir($path, 0777, true);
// }

if (file_put_contents($path, "Hellowwwww World")) {
    echo "✅ Write test succeeded!";
} else {
    echo "❌ Write test failed. Check folder permissions.";
}
