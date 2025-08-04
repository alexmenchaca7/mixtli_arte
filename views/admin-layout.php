<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MixtliArte | <?php echo $titulo; ?></title>

    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MixtliArte" />
    <link rel="manifest" href="/favicon/site.webmanifest" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo get_asset('app.css'); ?>">
</head>
<body class="dashboard">
    <div class="overlay"></div> <?php 
        include_once __DIR__ .'/templates/admin-header.php';
    ?>
    <div class="dashboard__grid">
        <?php
            include_once __DIR__ .'/templates/admin-sidebar.php';  
        ?>

        <main class="dashboard__contenido">
            <?php 
                echo $contenido; 
            ?> 
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="<?php echo get_asset('app.js'); ?>" defer></script>
</body>
</html>