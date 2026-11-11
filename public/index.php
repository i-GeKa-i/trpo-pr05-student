<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Model/Category.php';
require_once __DIR__ . '/../src/Service/CatalogService.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$fatal = null;
$service = null;
try {
    $service = new CatalogService(__DIR__ . '/../data/products.json', __DIR__ . '/../data/categories.json');
} catch (RuntimeException $ex) {
    $fatal = $ex->getMessage();
}

// Обработка фильтра (отдельно от моделей). Некорректная категория -> сообщение, а не warning.
$filterError = null;
$categoryId = null;
$onlyAvailable = isset($_GET['available']);
$rawCategory = trim((string)($_GET['category'] ?? ''));
if ($service !== null && $rawCategory !== '') {
    $categoryId = filter_var($rawCategory, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($categoryId === false || $service->category($categoryId) === null) {
        $filterError = 'Такой категории нет — показан весь каталог.';
        $categoryId = null;
    }
}

$products = $service?->filter($categoryId, $onlyAvailable) ?? [];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Каталог товаров — ПР №5</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="brand">Практическая работа 5</div>
    <span>ТРПО</span>
</header>
<main class="wrap">
    <section class="hero">
        <span class="pill">PHP ООП</span>
        <h1>Каталог товаров</h1>
        <p class="muted">Product, Category и CatalogService; данные из data/products.json и data/categories.json.</p>
    </section>

<?php if ($fatal !== null): ?>
    <p class="alert" role="alert">Ошибка загрузки данных: <?= e($fatal) ?></p>
<?php else: ?>
<?php if ($service->errors() !== []): ?>
    <div class="alert" role="alert">
        Пропущено некорректных строк dataset: <?= count($service->errors()) ?>
        <ul>
<?php foreach ($service->errors() as $err): ?>
            <li><?= e($err) ?></li>
<?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

    <form method="get" class="filter">
        <label>Категория
            <select name="category">
                <option value="">Все</option>
<?php foreach ($service->categories() as $c): ?>
                <option value="<?= $c->id() ?>"<?= $categoryId === $c->id() ? ' selected' : '' ?>><?= e($c->name()) ?></option>
<?php endforeach; ?>
            </select>
        </label>
        <label><input type="checkbox" name="available"<?= $onlyAvailable ? ' checked' : '' ?>> Только в наличии</label>
        <button class="btn" type="submit">Применить</button>
        <a href="index.php">Сбросить</a>
    </form>
<?php if ($filterError !== null): ?>
    <p class="alert" role="alert"><?= e($filterError) ?></p>
<?php endif; ?>

    <section class="grid" aria-label="Статистика">
        <div class="card"><b>Найдено</b><span class="muted"><?= count($products) ?> из <?= count($service->products()) ?></span></div>
        <div class="card"><b>Средняя цена</b><span class="muted"><?= e(number_format($service->averagePrice($products), 2, ',', ' ')) ?> ₽</span></div>
        <div class="card"><b>Категорий</b><span class="muted"><?= count($service->categories()) ?></span></div>
    </section>

<?php if ($products === []): ?>
    <p class="empty">По выбранным условиям товаров нет.</p>
<?php else: ?>
    <section class="grid products" aria-label="Товары">
<?php foreach ($products as $p): ?>
        <article class="card">
            <b><?= e($p->name()) ?></b>
            <span class="muted"><?= e($service->categoryOf($p)?->name() ?? 'Без категории') ?></span>
            <p class="price"><?= e($p->formattedPrice()) ?></p>
            <span class="pill<?= $p->isAvailable() ? '' : ' off' ?>"><?= $p->isAvailable() ? 'в наличии' : 'нет в наличии' ?></span>
        </article>
<?php endforeach; ?>
    </section>
<?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>
