<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
class Currency extends BaseEntity
{
    public const AUD = 'AUD';

    public function __construct(array $fields)
    {
        $this->name = $fields['name'] ?? null;
        $this->code = $fields['code'] ?? null;
        $this->symbol = $fields['symbol'] ?? null;
        $this->decimals = $fields['decimals'] ?? null;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'smallint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'code', type: 'string', length: 255, unique: true)]
    private $code;

    /**
     * @var string
     */
    #[ORM\Column(name: 'symbol', type: 'string', length: 255)]
    private $symbol;

    /**
     * @var string
     */
    #[ORM\Column(name: 'decimals', type: 'smallint')]
    private $decimals;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function setDecimals(string $decimals): self
    {
        $this->decimals = $decimals;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'code' => $this->getCode(),
            'symbol' => $this->getSymbol(),
            'decimals' => $this->getDecimals()
        ];
    }
}
