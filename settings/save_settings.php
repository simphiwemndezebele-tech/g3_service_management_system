<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


/* ==============================
   Check Form Submission
   ============================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}


/* ==============================
   Receive Form Data
   ============================== */

$company_name = trim($_POST['company_name'] ?? '');
$company_phone = trim($_POST['company_phone'] ?? '');
$company_email = trim($_POST['company_email'] ?? '');
$company_address = trim($_POST['company_address'] ?? '');


/* ==============================
   Validate Company Name
   ============================== */

if ($company_name === '') {
    die("Company name is required.");
}


/* ==============================
   Handle Logo Upload
   ============================== */

if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['company_logo']['error'] !== UPLOAD_ERR_OK) {
        die("Error uploading logo.");
    }


    /* Maximum file size: 2 MB */

    if ($_FILES['company_logo']['size'] > 2 * 1024 * 1024) {
        die("Logo must be smaller than 2 MB.");
    }


    /* Check actual image type */

    $image_info = getimagesize($_FILES['company_logo']['tmp_name']);

    if ($image_info === false) {
        die("The uploaded file is not a valid image.");
    }


    /* Allowed image types */

    $allowed_types = [
        IMAGETYPE_PNG,
        IMAGETYPE_JPEG
    ];

    if (!in_array($image_info[2], $allowed_types)) {
        die("Only PNG and JPG/JPEG images are allowed.");
    }


    /* Upload location */

    $upload_directory = "../assets/images/";


    /* Always save as logo.png */

    $logo_path = $upload_directory . "logo.png";


    /* Convert uploaded image to PNG */

    if ($image_info[2] === IMAGETYPE_PNG) {

        $image = imagecreatefrompng(
            $_FILES['company_logo']['tmp_name']
        );

    } elseif ($image_info[2] === IMAGETYPE_JPEG) {

        $image = imagecreatefromjpeg(
            $_FILES['company_logo']['tmp_name']
        );

    }


    if (!$image) {
        die("Unable to process the uploaded logo.");
    }


    /* Save as PNG */

    if (!imagepng($image, $logo_path)) {

        imagedestroy($image);

        die("Unable to save the logo.");

    }


    imagedestroy($image);
}


/* ==============================
   Update Company Settings
   ============================== */

$sql = "UPDATE settings SET
        company_name=?,
        company_phone=?,
        company_email=?,
        company_address=?
        WHERE id=1";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $company_name,
    $company_phone,
    $company_email,
    $company_address
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: index.php");
    exit();

} else {

    echo "Error saving settings: " . mysqli_error($conn);

}

?>