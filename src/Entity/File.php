<?php

namespace App\Entity;

use App\Service\File\FileService;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * File
 */
#[ORM\Table(name: 'file')]
#[ORM\Entity(repositoryClass: 'App\Repository\FileRepository')]
class File extends BaseEntity
{
    public const EXTENSION_CSV = 'csv';
    public const EXTENSION_XLS = 'xls';
    public const EXTENSION_XLSX = 'xlsx';
    public const EXTENSION_TXT = 'txt';

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'path',
        'remotePath',
        'displayName',
        'createdAt',
        'createdById',
        'teamId',
        'original',
        'mimeType',
        'extension',
        'url',
        'isStored',
    ];

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('name', $include, true)) {
            $data['name'] = $this->name;
        }
        if (in_array('displayName', $include, true)) {
            $data['displayName'] = $this->displayName;
        }
        if (in_array('path', $include, true)) {
            $data['path'] = $this->path;
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->createdAt);
        }
        if (in_array('createdById', $include, true)) {
            $data['createdById'] = $this->getCreatedById();
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedByArray();
        }
        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->getTeamId();
        }
        if (in_array('remotePath', $include, true)) {
            $data['remotePath'] = $this->getRemotePath();
        }
        if (in_array('original', $include, true)) {
            $data['original'] = $this->getOriginal() ? $this->getOriginal()->toArray() : null;
        }
        if (in_array('thumbnail', $include, true)) {
            $data['thumbnail'] = $this->getThumbnail() ? $this->getThumbnail()->toArray() : null;
        }
        if (in_array('mimeType', $include, true)) {
            $data['mimeType'] = $this->getMimeType();
        }
        if (in_array('extension', $include, true)) {
            $data['extension'] = $this->getExtension();
        }
        if (in_array('url', $include, true)) {
            $data['url'] = $this->getUrl();
        }
        if (in_array('isStored', $include, true)) {
            $data['isStored'] = $this->isStored();
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
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
    #[ORM\Column(name: 'display_name', type: 'string', length: 255, nullable: true)]
    private $displayName;

    /**
     * @var string
     */
    #[ORM\Column(name: 'path', type: 'string', length: 255)]
    private $path;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $createdBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'remote_path', type: 'string', length: 500, nullable: true)]
    private ?string $remotePath = null;

    /**
     * @var File|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\File', inversedBy: 'thumbnail')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?File $original = null;

    /**
     * @var File|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\File', mappedBy: 'original', fetch: 'EXTRA_LAZY')]
    private ?File $thumbnail;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'mime_type', type: 'string', length: 255, nullable: true)]
    private ?string $mimeType;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'size', type: 'bigint', nullable: true)]
    private ?float $size;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'url', type: 'string', length: 500, nullable: true)]
    private ?string $url = null;

    /**
     * @param string $name
     * @param string|null $path
     * @param User|null $createdBy
     */
    public function __construct(string $name, ?string $path, User $createdBy = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->createdBy = $createdBy ?? null;
        $this->createdAt = Carbon::now('UTC');
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return File
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return int|null
     */
    public function getCreatedById(): ?int
    {
        return $this->createdBy ? $this->createdBy->getId() : null;
    }

    /**
     * @return array|null
     */
    public function getCreatedByArray(): ?array
    {
        return $this->createdBy ? $this->createdBy->toArray() : null;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return File
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @param null $filePath
     * @return mixed
     */
    public function getExtension($filePath = null)
    {
        $file = $filePath ? $filePath : $this->getName();

        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType ?: (!$this->isRemote() ? FileService::getMimeType($this->getFullPath()) : null);
    }

    /**
     * @return int|null
     */
    public function getTeamId()
    {
        return $this->getCreatedBy() ? $this->getCreatedBy()->getTeam()->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getRemotePath(): ?string
    {
        return $this->remotePath;
    }

    /**
     * @param string|null $remotePath
     */
    public function setRemotePath(?string $remotePath): void
    {
        $this->remotePath = $remotePath;
    }

    /**
     * @return bool
     */
    public function isRemote(): bool
    {
        return !is_null($this->getRemotePath());
    }

    /**
     * @return File|null
     */
    public function getOriginal(): ?File
    {
        return $this->original;
    }

    /**
     * @param File|null $original
     */
    public function setOriginal(?File $original): void
    {
        $this->original = $original;
    }

    /**
     * @return bool
     */
    public function isOriginal(): bool
    {
        return !$this->getOriginal();
    }

    /**
     * @return File|null
     */
    public function getThumbnail(): ?File
    {
        return $this->thumbnail;
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->getPath() . $this->getName();
    }

    /**
     * @param string|null $mimeType
     * @return File
     */
    public function setMimeType(?string $mimeType): File
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getSize(): ?float
    {
        return $this->size;
    }

    /**
     * @param float|null $size
     */
    public function setSize(?float $size): void
    {
        $this->size = $size;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function isStored(): bool
    {
        return boolval($this->getUrl());
    }
}

