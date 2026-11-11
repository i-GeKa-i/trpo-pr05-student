# Чек-лист перед сдачей

- [x] Проект запускается по README (`php -S 127.0.0.1:8000 -t public`)
- [x] Файлы dataset используются: `products.json`, `categories.json` читаются `CatalogService`; `class_requirements.csv` определяет свойства классов
- [x] PHP warnings/notices нет (проверено на `/`, `?category=2`, `?category=abc&available=on`, `?category=99`)
- [x] Есть состояния для пустого результата, неверной категории и битых строк dataset
- [x] Структура: Model / Service / public, без дублирования
- [x] `php tests/check.php` — 20 проверок, код выхода 0
- [x] Несколько осмысленных коммитов в истории Git
