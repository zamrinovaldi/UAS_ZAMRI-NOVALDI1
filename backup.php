<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// One-click Backup script
$tables = array();
$result = mysqli_query($koneksi, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$return = "";
foreach ($tables as $table) {
    $result = mysqli_query($koneksi, "SELECT * FROM $table");
    $num_fields = mysqli_num_fields($result);

    $return .= "DROP TABLE IF EXISTS $table;";
    $row2 = mysqli_fetch_row(mysqli_query($koneksi, "SHOW CREATE TABLE $table"));
    $return .= "\n\n" . $row2[1] . ";\n\n";

    for ($i = 0; $i < $num_fields; $i++) {
        while ($row = mysqli_fetch_row($result)) {
            $return .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) {
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= '""';
                }
                if ($j < ($num_fields - 1)) {
                    $return .= ',';
                }
            }
            $return .= ");\n";
        }
    }
    $return .= "\n\n\n";
}

log_activity("Database Backup", "Berhasil mencadangkan seluruh basis data.");

// Download file
$filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
header('Content-type: application/sql');
header('Content-Disposition: attachment; filename=' . $filename);
echo $return;
exit;
?>
