<?php

declare(strict_types=1);

final class Product
{
    public function __construct(
        private int $id,
        private string $name,
        private float $price,
        private int $categoryId,
        private bool $active = true
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException("id товара должен быть положительным, получено: $id");
        }
        $this->name = trim($name);
        if ($this->name === '') {
            throw new InvalidArgumentException("У товара #$id пустое название");
        }
        if ($price < 0) {
            throw new InvalidArgumentException("У товара #$id отрицательная цена: $price");
        }
        if ($categoryId <= 0) {
            throw new InvalidArgumentException("У товара #$id некорректный categoryId: $categoryId");
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

    public function price(): float
    {
        return $this->price;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }



    public function isAvailable(): bool
    {
        return $this->active;
    }

    public function belongsTo(Category $category): bool
    {
        return $this->categoryId === $category->id();
    }

    public function priceWithDiscount(float $percent): float
    {
        if ($percent < 0 || $percent > 100) {
            throw new InvalidArgumentException("Скидка должна быть в диапазоне 0..100, получено: $percent");
        }
        return round($this->price * (1 - $percent / 100), 2);
    }

    public function formattedPrice(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' ₽';
    }

    public function isFree(): bool
    {
        return $this->price === 0.0;
    }
}
