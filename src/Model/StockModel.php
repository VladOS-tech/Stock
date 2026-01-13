<?php
declare(strict_types=1);

namespace Warehouse\Model;

use DomainException;
use Warehouse\Contracts\StockModelInterface;

class StockModel implements StockModelInterface
{
    public function __construct(
        public readonly int $id,
        public string $ctid,
        public string $sku,
        public string $state,
        public ?float $price = null
    ) {}

    public function hold(?float $price, string $orderId): void
    {
        if ($this->state !== 'Stock') {
            throw new DomainException("Нельзя зарезервировать $this->sku");
        }

        $this->state = "Hold/$orderId";
        if ($price !== null) {
            $this->price = $price;
        }
    }

    public function confirm(): void
    {
        if (!str_starts_with($this->state, 'Hold/')) {
            throw new DomainException("Нельзя подтвердить $this->sku");
        }

        $this->state = 'Sold';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            ctid: $data['ctid'],
            sku: $data['sku'],
            state: $data['state'],
            price: isset($data['price']) ? (float)$data['price'] : null
        );
    }
}
