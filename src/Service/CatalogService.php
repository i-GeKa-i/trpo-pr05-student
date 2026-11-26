<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Product.php';
require_once __DIR__ . '/../Model/Category.php';


final class CatalogService
{
    
    private array $products = [];
    private array $categories = [];
    private array $errors = [];

    public function __construct(string $productsPath, string $categoriesPath)
    {
        foreach ($this->readJson($categoriesPath) as $i => $row) {
            try {
                $category = new Category((int)($row['id'] ?? 0), (string)($row['name'] ?? ''));
                $this->categories[$category->id()] = $category;
            } catch (InvalidArgumentException $e) {
                $this->errors[] = "categories.json, строка " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        foreach ($this->readJson($productsPath) as $i => $row) {
            try {
                $product = new Product(
                    (int)($row['id'] ?? 0),
                    (string)($row['name'] ?? ''),
                    (float)($row['price'] ?? -1),
                    (int)($row['category_id'] ?? 0),
                    (bool)($row['active'] ?? true) 
                );
                if (!isset($this->categories[$product->categoryId()])) {
                    throw new InvalidArgumentException("Товар #{$product->id()}: неизвестная категория {$product->categoryId()}");
                }
                $this->products[] = $product;
            } catch (InvalidArgumentException $e) {
                $this->errors[] = "products.json, строка " . ($i + 1) . ": " . $e->getMessage();
            }
        }
    }


    public function products(): array
    {
        return $this->products;
    }


    public function categories(): array
    {
        return array_values($this->categories);
    }

    public function category(int $id): ?Category
    {
        return $this->categories[$id] ?? null;
    }

    public function categoryOf(Product $product): ?Category
    {
        return $this->category($product->categoryId());
    }


    public function errors(): array
    {
        return $this->errors;
    }


    public function filter(?int $categoryId = null, bool $onlyAvailable = false): array
    {
        return array_values(array_filter(
            $this->products,
            fn(Product $p) => ($categoryId === null || $p->categoryId() === $categoryId)
                && (!$onlyAvailable || $p->isAvailable())
        ));
    }


    public function averagePrice(array $products): float
    {
        if ($products === []) {
            return 0.0;
        }
        return array_sum(array_map(fn(Product $p) => $p->price(), $products)) / count($products);
    }


    private function readJson(string $path): array
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Не удалось прочитать файл: $path");
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException("Файл $path содержит некорректный JSON: " . json_last_error_msg());
        }
        return array_values(array_filter($data, 'is_array'));
    }
}
