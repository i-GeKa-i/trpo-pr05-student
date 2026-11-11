# Практическая работа №5. PHP ООП: классы, конструкторы и модели предметной области

Дисциплина: Технологии разработки программного обеспечения.

PHP-приложение «Каталог товаров»: данные из `data/products.json` и `data/categories.json` превращаются в объекты `Product` и `Category`, `CatalogService` фильтрует их и считает среднюю цену, `public/index.php` выводит карточки и форму фильтра.

## Технологии

PHP 8.1+ (проверено на 8.4), без зависимостей и Composer. Вёрстка — чистые HTML/CSS.

## Запуск

```bash
git clone https://github.com/i-GeKa-i/trpo-pr05-student.git
cd trpo-pr05-student
php -S 127.0.0.1:8000 -t public
```

Открыть <http://127.0.0.1:8000/>.

Проверки (код выхода 0 — всё хорошо):

```bash
php tests/check.php
```

## Структура

```
public/index.php            # вывод: HTML, обработка формы фильтра
public/style.css
src/Model/Product.php       # товар: id, name, price, categoryId, active
src/Model/Category.php      # категория: id, name
src/Service/CatalogService.php  # чтение JSON, фильтр, агрегаты
data/                       # dataset: products.json, categories.json, class_requirements.csv
tests/check.php             # проверки без внешних библиотек
docs/checklist.md           # чек-лист сдачи
```

## Схема и ответственность

```
products.json ─┐
categories.json┴─► CatalogService ──► Product / Category ──► index.php ──► HTML
                   (читает, строит,    (данные + поведение,    (экранирует,
                    фильтрует, считает) валидация в __construct) выводит)
```

- **Product** — состояние товара и предметные методы: `isAvailable()`, `belongsTo(Category)`, `priceWithDiscount()`, `formattedPrice()`, `isFree()`. Связь с категорией — через `categoryId`. Некорректные значения (пустое имя, отрицательная цена, id ≤ 0) → `InvalidArgumentException`.
- **Category** — id и название.
- **CatalogService** — читает JSON, создаёт объекты, пропускает некорректные строки (причины доступны через `errors()`), `filter()` по категории/наличию, `averagePrice()`.
- **index.php** — только представление: `filter_var` для параметра, `htmlspecialchars` для вывода, состояния «пусто» и «ошибка».

Поля `class_requirements.csv` (`Product`: id, name, price, categoryId; `Category`: id, name) реализованы 1:1. Необязательное поле `active` из `products.json` отсутствующим считается `true`.

## Что изменилось после шага 4 (предметные методы)

Модель перестала быть «мешком геттеров»: логика наличия, скидки, формата цены и принадлежности категории живёт в `Product`, а `index.php` лишь вызывает `formattedPrice()` и `isAvailable()`. Формат хранения цены можно поменять, не трогая HTML.

## Сценарий проверки

1. Открыть главную — 12 карточек, средняя цена 1 192,75 ₽.
2. Выбрать категорию «Оборудование» — 4 товара.
3. Включить «Только в наличии» — неактивные товары 5 и 10 скрыты.
4. Открыть `/?category=abc` или `/?category=99` — сообщение «Такой категории нет», показан весь каталог, без PHP warning.
5. Сломать строку в `data/products.json` (например, очистить `name`) — над каталогом появится блок с пропущенными строками, остальные товары выводятся. Состояние «товаров нет» появится, если в категории не останется подходящих товаров.

## Почему ручной `require_once` неудобен при росте проекта

Каждый класс приходится подключать вручную и в правильном порядке (`CatalogService` требует `Product` и `Category`). С десятками классов список `require_once` растёт, легко забыть файл или получить циклы. Решение — автозагрузка PSR-4 (`spl_autoload_register` / Composer), чтобы классы подключались по имени.

## Ответы на контрольные вопросы (кратко)

- **Вход:** `data/products.json`, `data/categories.json` (+ GET-параметры фильтра).
- **Преобразование в интерфейс:** `CatalogService` → объекты → циклы в `public/index.php`.
- **Проверенное ошибочное состояние:** неизвестная/нечисловая категория, битые строки dataset, пустая выдача (см. `tests/check.php`).
- **Конструктор** нужен, чтобы объект не мог существовать в невалидном состоянии.
- **PHP vs JavaScript:** PHP выполняется на сервере и отдаёт готовый HTML; JS в браузере работает уже после загрузки страницы.
