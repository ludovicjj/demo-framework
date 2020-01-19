<?= $renderer->render('header.php', ['title' => $slug]); ?>

    <h1>Bienvenue sur l'article <?= $slug; ?></h1>

<?= $renderer->render('footer.php'); ?>