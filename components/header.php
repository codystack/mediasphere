<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ./'); // or login page
    exit();
}

$fullName = htmlspecialchars($_SESSION['admin_name'] ?? '');
$userEmail = htmlspecialchars($_SESSION['admin_email'] ?? '');
$firstName = htmlspecialchars($_SESSION['first_name'] ?? '');
$lastName = htmlspecialchars($_SESSION['last_name'] ?? '');
$userPhone = htmlspecialchars($_SESSION['admin_phone'] ?? '');
$avatar = htmlspecialchars($_SESSION['admin_picture'] ?? '');
$designation = htmlspecialchars($_SESSION['designation'] ?? '');
?>

<!doctype html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="color-scheme" content="dark light">
    <link rel="shortcut icon" href="./assets/img/ms-favicon.svg">

    <title>Media Spahere Limited&trade;</title>

    <link rel="stylesheet" type="text/css" href="./assets/css/main.css">
    <link rel="stylesheet" type="text/css" href="./assets/css/utilities.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script defer="defer" data-domain="webpixels.works" src="https://plausible.io/js/script.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />

</head>

<body>