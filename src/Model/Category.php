<?php

declare(strict_types=1);

/** Категория товаров. Товар ссылается на неё по id (Product::categoryId). */
final class Category
{
    public function __construct(
        private int $id,
        private string $name
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException("id категории должен быть положительным, получено: $id");
        }
        $this->name = trim($name);
        if ($this->name === '') {
            throw new InvalidArgumentException("У категории #$id пустое название");
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}
