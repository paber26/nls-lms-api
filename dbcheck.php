<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=nls_lms;charset=utf8', 'root', '');
$stmt = $db->query("SELECT id, judul, tipe, videoUrl FROM materi WHERE id = 10");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
