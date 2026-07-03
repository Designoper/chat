<?php

declare(strict_types=1);

require_once __DIR__ . '/SQL.php';

enum FileTypes
{
    case Image;
    case Audio;
    case Video;
}

abstract readonly class File extends SQL
{
    private const string COMMON_FILE_PATH = '/private';
    protected const string DEFAULT_IMAGE = self::COMMON_FILE_PATH . '/default/default.jpg';

    protected string $extraDirectories;
    protected string $uniqueFilename;

    protected ?array $file;
    protected bool $deleteCheckbox;

    private string $fileUrl;

    protected function __construct()
    {
        parent::__construct();
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
            if ((int) $files['error'] === 0) {
                $newArray[] = $files;
            }
        }

        return $newArray;
    }

    // MARK: SET UNIQUE FILENAME

    private function setUniqueFilename(string $originalFilename): void
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $this->uniqueFilename = $filename . '-' . bin2hex(random_bytes(2)) . '.' . $extension;
    }

    // MARK: UPLOAD FILENAME

    protected function uploadFileName(): ?string
    {
        if ($this->file === null) {
            return null;
        }

        $this->setUniqueFilename($this->file['name']);

        return $this->extraDirectories . $this->uniqueFilename;
    }

    // MARK: UPLOAD FILE

    protected function uploadFile(): void
    {
        if ($this->file === null) {
            return;
        }

        $folderDestination = $_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH . $this->extraDirectories;

        if (!file_exists($folderDestination)) {
            mkdir($folderDestination, 0755, true);
        }

        $finalDestination = $folderDestination . $this->uniqueFilename;

        move_uploaded_file($this->file['tmp_name'], $finalDestination);
    }

    // MARK: UPDATE FILENAME

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

        $imagePath = self::COMMON_FILE_PATH . $this->extraDirectories . $this->uniqueFilename;

        return $imagePath;
    }

    // MARK: UPDATE FILE

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

    // MARK: DELETE FILE

    protected function deleteFile(?string $filePath): void
    {
        if ($filePath !== null) {
            unlink($_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH . $filePath);
        }
    }

    // MARK: DELETE ALL FILES

    protected function deleteAllFiles(): void
    {
        $folderPath = $_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH . $this->extraDirectories;

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

    // MARK: GET FILE URL

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

    // MARK: SHOW FILE

    protected function showFile(): void
    {
        // Ruta base absoluta de la carpeta private
        $base = realpath($_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH);

        // Normalizar la ruta solicitada
        $rutaSolicitada = realpath($_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH . $_GET['f']);

        // Validación: el archivo debe estar dentro de /private
        if (!$rutaSolicitada || !str_starts_with($rutaSolicitada, $base)) {
            $this->status = 403;
            $this->errors->setIntegrityError("Acceso no permitido");
            $this->checkIntegrityErrors();
        }

        if (!is_file($rutaSolicitada)) {
            $this->status = 404;
            $this->errors->setIntegrityError("Archivo no encontrado");
            $this->checkIntegrityErrors();
        }

        // Validar MIME real
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($rutaSolicitada);

        // Permitir solo imagen/audio/video
        if (
            !str_starts_with($mime, 'image/') &&
            !str_starts_with($mime, 'audio/') &&
            !str_starts_with($mime, 'video/')
        ) {
            $this->status = 403;
            $this->errors->setIntegrityError("Tipo de archivo no permitido");
            $this->checkIntegrityErrors();
        }

        // Enviar archivo
        header("Content-Type: $mime");
        readfile($rutaSolicitada);
        exit;
    }
}
