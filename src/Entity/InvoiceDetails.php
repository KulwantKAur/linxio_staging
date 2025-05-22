<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceDetailsRepository;

#[ORM\Table(name: 'invoice_details')]
#[ORM\Entity(repositoryClass: InvoiceDetailsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class InvoiceDetails extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'key',
        'quantity',
        'price',
        'total',
    ];

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Invoice', inversedBy: 'details')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    private $invoice;

    
    #[ORM\Column(type: 'string')]
    #[ORM\Id]
    private $key;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $quantity = 0;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $price = 0;

    #[ORM\Column(type: 'decimal', options: ['default' => 0])]
    private $total = 0;

    public function __construct(array $fields)
    {
        $this->invoice = $fields['invoice'];
        $this->key = $fields['key'];
        $this->quantity = ceil((($fields['quantity'] ?? 0) * 10) . '') / 10;
        $this->price = $fields['price'] ?? 0;
        $this->total = $fields['total'] ?? 0;
    }

    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('key', $include)) {
            $data['key'] = $this->getKey();
        }

        if (in_array('quantity', $include)) {
            $data['quantity'] = $this->getQuantity();
        }

        if (in_array('price', $include)) {
            $data['price'] = $this->getPrice();
        }

        if (in_array('total', $include)) {
            $data['total'] = $this->getTotal();
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total): void
    {
        $this->total = $total;
    }
}
