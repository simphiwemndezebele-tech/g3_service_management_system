<?php
include("../config/db.php");

if (isset($_GET['customer_id'])) {

    $customer_id = intval($_GET['customer_id']);

    $sql = "SELECT id, asset_number, machine_model
            FROM machines
            WHERE customer_id = ?
            ORDER BY asset_number ASC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    echo '<option value="">-- Select Machine --</option>';

    while ($row = mysqli_fetch_assoc($result)) {

        echo '<option value="' . $row['id'] . '">'
            . $row['asset_number'] . ' - ' . $row['machine_model']
            . '</option>';
    }

} else {

    echo '<option value="">-- Select Customer First --</option>';

}
?>