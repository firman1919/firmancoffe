<?php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = ""; 
$DB_NAME = "firmancoffe";

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Gagal koneksi MySQL: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

function rupiah($n) {
    return "Rp " . number_format($n,0,',','.');
}
?>

<?php
// fungsi buat bikin URL gambar otomatis
function imageUrl($path) {
    if (empty($path)) return null;

    // base URL kamu, sesuaikan kalau beda
    $base = "http://localhost/firmancoffe/";

    return $base . ltrim($path, '/');
}

?>
