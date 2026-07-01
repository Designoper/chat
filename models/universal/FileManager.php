<?php

declare(strict_types=1);

require_once __DIR__ . '/SQL.php';

readonly class FileManager extends SQL
{
    private const string IMAGE_PATH = '/assets/img/';
    public const string DEFAULT_IMAGE = self::IMAGE_PATH . 'default/default.jpg';

    public string $extraDirectories;
    public string $uniqueFilename;

    public ?array $file;
    public bool $deleteCheckbox;

    public function __construct()
    {
        parent::__construct();
    }

    // MARK: SETTERS

    private function setUniqueFilename(string $originalFilename): void
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $this->uniqueFilename = $filename . '-' . bin2hex(random_bytes(2)) . '.' . $extension;
    }

    // MARK: FLATTEN FILES ARRAY

    protected function flattenFilesArray(string $inputFileName): array
    {
        if (!isset($_FILES[$inputFileName])) {
            return [];
        }

        $files = $_FILES[$inputFileName];
        $newArray = [];

        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ((int) $files['error'][$i] === 0) {
                    $newArray[] = [
                        'name'     => $files['name'][$i],
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i],
                    ];
                }
            }
        } else {
            if ((int)$files['error'] === 0) {
                $newArray[] = $files;
            }
        }

        return $newArray;
    }

    protected function uploadFileName(): ?string
    {
        if ($this->file === null) {
            return null;
        }

        $this->setUniqueFilename($this->file['name']);

        return self::IMAGE_PATH . $this->extraDirectories . $this->uniqueFilename;
    }

    protected function uploadFile(): void
    {
        if ($this->file === null) {
            return;
        }

        $folderDestination = $_SERVER['DOCUMENT_ROOT'] . self::IMAGE_PATH . $this->extraDirectories;

        if (!file_exists($folderDestination)) {
            mkdir($folderDestination, 0755, true);
        }

        $finalDestination = $folderDestination . $this->uniqueFilename;

        move_uploaded_file($this->file['tmp_name'], $finalDestination);
    }

    protected function updateFileName(string $column, string $table, string $primaryKey, string $primaryKeyValue): ?string
    {
        if ($this->file === null) {
            if ($this->deleteCheckbox === true) {
                return null;
            }
            $fileUrl = $this->getFileUrl($column, $table, $primaryKey, $primaryKeyValue);
            return $fileUrl;
        }

        $this->setUniqueFilename($this->file['name']);

        $imagePath = self::IMAGE_PATH . $this->extraDirectories . $this->uniqueFilename;

        return $imagePath;
    }

    protected function updateFile(?string $filePath): void
    {
        if ($this->file === null) {
            if ($this->deleteCheckbox === true) {
                $this->deleteFile($filePath);
                return;
            }
            return;
        }

        $this->deleteFile($filePath);
        $this->uploadFile();
    }

    protected function deleteFile(?string $filePath): void
    {
        if ($filePath !== null) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $filePath);
        }
    }

    protected function deleteAllFiles(): void
    {
        $folderPath = $_SERVER['DOCUMENT_ROOT'] . self::IMAGE_PATH . $this->extraDirectories;

        if (!is_dir($folderPath)) {
            return;
        }

        $directoryIterator = new DirectoryIterator($folderPath);

        foreach ($directoryIterator as $file) {
            if ($file->isFile()) {
                unlink($file->getRealPath());
            }
        }
    }

    protected function getFileUrl(string $column, string $table, string $primaryKey, string $primaryKeyValue): string|null|false
    {
        $query =
            "SELECT $column
            FROM $table
            WHERE $primaryKey = ?";

        $fileUrl = $this->executeQuery(
            $query,
            "s",
            [
                $primaryKeyValue
            ],
            SqlReturn::FetchColumn
        );

        return $fileUrl;
    }
}
