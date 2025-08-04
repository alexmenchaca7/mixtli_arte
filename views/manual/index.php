<?php if($_SESSION['rol'] === 'vendedor'): ?>
    <h2 class="dashboard__heading"><?php echo $titulo; ?></h2>
<?php endif; ?>

<div class="dashboard__contenedor" style="height: 90vh;">
    <?php if (!empty($pdfPath)): ?>
        <iframe src="<?php echo $pdfPath; ?>" width="100%" height="100%" style="border: none;"></iframe>
    <?php else: ?>
        <p class="t-align-center">El manual de usuario no está disponible en este momento.</p>
    <?php endif; ?>
</div>