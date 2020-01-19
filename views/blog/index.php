<?= $renderer->render('header.php', ['title' => 'Blog']); ?>

<h1>Bienvenue sur le Blog</h1>

<ul>
    <li>
        <a href="<?= $router->generateUri('blog.show', ['slug' => 'demo']); ?>">
            Article 1
        </a>
    </li>
    <li>article 1</li>
    <li>article 1</li>
    <li>article 1</li>
    <li>article 1</li>
</ul>

<?= $renderer->render('footer.php'); ?>