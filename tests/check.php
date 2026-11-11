<?php

declare(strict_types=1);

// Запуск: php tests/check.php  (код выхода 0 — все проверки пройдены)
require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Model/Category.php';
require_once __DIR__ . '/../src/Service/CatalogService.php';

$failed = 0;
function check(string $name, bool $ok): void
{
    global $failed;
    echo ($ok ? '[ OK ] ' : '[FAIL] ') . $name . PHP_EOL;
    $failed += $ok ? 0 : 1;
}
function throwsInvalid(callable $fn): bool
{
    try {
        $fn();
    } catch (InvalidArgumentException) {
        return true;
    }
    return false;
}

// Некорректный конструктор -> InvalidArgumentException
check('пустое имя товара', throwsInvalid(fn() => new Product(1, '   ', 10.0, 1)));
check('отрицательная цена', throwsInvalid(fn() => new Product(1, 'X', -1.0, 1)));
check('id <= 0', throwsInvalid(fn() => new Product(0, 'X', 1.0, 1)));
check('скидка вне 0..100', throwsInvalid(fn() => (new Product(1, 'X', 100.0, 1))->priceWithDiscount(150)));
check('пустое имя категории', throwsInvalid(fn() => new Category(1, '')));

// Методы предметной области
$p = new Product(10, 'Keyboard', 3500, 2);
check('скидка 10%', $p->priceWithDiscount(10) === 3150.0);
check('formattedPrice', $p->formattedPrice() === '3 500,00 ₽');
check('belongsTo', $p->belongsTo(new Category(2, 'Оборудование')));
check('active по умолчанию', $p->isAvailable());

// Сервис на реальном dataset
$s = new CatalogService(__DIR__ . '/../data/products.json', __DIR__ . '/../data/categories.json');
check('загружено 12 товаров', count($s->products()) === 12);
check('загружено 3 категории', count($s->categories()) === 3);
check('нет ошибок в dataset', $s->errors() === []);
check('фильтр категории 2 -> 4 товара', count($s->filter(2)) === 4);
check('только в наличии -> 10', count($s->filter(null, true)) === 10);
check('средняя цена всех = 1192.75', abs($s->averagePrice($s->products()) - 1192.75) < 0.001);
check('средняя цена пустого набора = 0', $s->averagePrice([]) === 0.0);
check('категория товара', $s->categoryOf($s->products()[0])?->name() === 'Оборудование');

// Битый dataset: плохие строки пропускаются и попадают в errors()
$tmp = tempnam(sys_get_temp_dir(), 'p');
file_put_contents($tmp, json_encode([
    ['id' => 1, 'name' => 'Ок', 'price' => 5, 'category_id' => 1],
    ['id' => 2, 'name' => '', 'price' => 5, 'category_id' => 1],
    ['id' => 3, 'name' => 'Нет категории', 'price' => 5, 'category_id' => 99],
]));
$bad = new CatalogService($tmp, __DIR__ . '/../data/categories.json');
unlink($tmp);
check('битые строки пропущены', count($bad->products()) === 1 && count($bad->errors()) === 2);
check('active по умолчанию из dataset', $bad->products()[0]->isAvailable());

try {
    new CatalogService('/no/such.json', __DIR__ . '/../data/categories.json');
    check('отсутствующий файл -> RuntimeException', false);
} catch (RuntimeException) {
    check('отсутствующий файл -> RuntimeException', true);
}

exit($failed === 0 ? 0 : 1);
