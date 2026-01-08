<?php

declare(strict_types=1);

namespace App\Domain\Shared\File\Entity;

use App\Domain\Shared\File\ValueObject\FileType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity]
#[ORM\Table(name: 'file')]
class File
{
    public const string STORAGE_FOLDER = 'files';

    public const int MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5 MB

    public const array IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $filename;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $originalName;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    private string $mimeType;

    #[ORM\Column(type: 'string', length: 50, nullable: false, enumType: FileType::class)]
    private FileType $type;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $width = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $height = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $extension = null;

    private ?UploadedFile $uploadedFile = null;

    private function __construct(
        string $filename,
        string $originalName,
        string $mimeType,
        FileType $type,
    ) {
        $this->id = Uuid::uuid4()->toString();
        $this->filename = $filename;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->type = $type;
        $this->extension = pathinfo($originalName, \PATHINFO_EXTENSION);
    }

    public static function createFromUploadedFile(
        UploadedFile $uploadedFile,
        FileType $type,
    ): self {
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getMimeType() ?? 'application/octet-stream';

        $extension = $uploadedFile->guessExtension() ?? pathinfo($originalName, \PATHINFO_EXTENSION);
        $filename = \sprintf(
            '%s_%s.%s',
            bin2hex(random_bytes(6)) . '_' . str_replace('.', '_', uniqid('', true)),
            time(),
            $extension
        );

        $file = new self($filename, $originalName, $mimeType, $type);
        $file->uploadedFile = $uploadedFile;

        if ($type === FileType::IMAGE) {
            $file->extractImageDimensions($uploadedFile);
        }

        return $file;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getType(): FileType
    {
        return $this->type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getUploadedFile(): ?UploadedFile
    {
        return $this->uploadedFile;
    }

    public function getFullPath(string $baseDir): string
    {
        return $baseDir . '/' . self::STORAGE_FOLDER . '/' . $this->filename;
    }

    public function getRelativePath(): string
    {
        return self::STORAGE_FOLDER . '/' . $this->filename;
    }

    private function extractImageDimensions(UploadedFile $file): void
    {
        try {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo !== false) {
                $this->width = $imageInfo[0];
                $this->height = $imageInfo[1];
            }
        } catch (\Exception) {
            // Ignore
        }
    }
}
