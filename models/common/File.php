<?php

declare(strict_types=1);

require_once __DIR__ . '/SQL.php';

enum FileTypes: string
{
    case Image = 'image';
    case Audio = 'audio';
    case Video = 'video';
    case Text = 'text';
}

abstract readonly class File extends SQL
{
    private const string COMPRESSED_IMAGE_EXTENSION = 'avif';
    private const string COMMON_FILE_PATH = '/private/';
    protected const string DEFAULT_IMAGE = self::COMMON_FILE_PATH . 'default/default.jpg';

    protected string $extraDirectories;
    protected string $uniqueFilename;

    protected ?array $file;
    protected bool $deleteCheckbox;

    private string $fileUrl;

    protected function __construct()
    {
        parent::__construct();
    }

    // ============================================================================
    // MARK: FLATTEN FILES ARRAY
    // ============================================================================
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

    // ============================================================================
    // MARK: SET UNIQUE FILENAME
    // ============================================================================
    private function setUniqueFilename(string $originalFilename, FileTypes $filetype): void
    {
        if ($filetype === FileTypes::Image) {
            $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION)) === 'gif'
                ? 'gif'
                : self::COMPRESSED_IMAGE_EXTENSION;
        } else $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        $filename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $this->uniqueFilename = $filename . '-' . bin2hex(random_bytes(2)) . '.' . $extension;
    }

    // ============================================================================
    // MARK: UPLOAD FILENAME
    // ============================================================================
    protected function uploadFileName(FileTypes $filetype): ?string
    {
        if ($this->file === null) {
            return null;
        }

        $this->setUniqueFilename($this->file['name'], $filetype);

        return $this->extraDirectories . $this->uniqueFilename;
    }

    // ============================================================================
    // MARK: UPLOAD FILE
    // ============================================================================
    protected function uploadFile(FileTypes $filetype): void
    {
        if ($this->file === null) {
            return;
        }

        $folderDestination = $_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH . $this->extraDirectories;

        if (!file_exists($folderDestination)) {
            mkdir($folderDestination, 0755, true);
        }

        $finalDestination = $folderDestination . $this->uniqueFilename;

        // 1. Forzamos minúsculas para evitar fallos con extensiones tipo .GIF o .Png
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));

        if ($filetype === FileTypes::Image && $extension !== 'gif') {
            $optimized = new Imagick($this->file['tmp_name']);
            // Eliminamos perfiles EXIF/color innecesarios para reducir drásticamente el peso
            $optimized->stripImage();
            $ancho_original = $optimized->getImageWidth();
            $ancho_deseado = 800;

            if ($ancho_original > $ancho_deseado) {
                $optimized->scaleImage($ancho_deseado, 0);
            }
            // Forzamos explícitamente el formato de salida en el buffer de Imagick
            $optimized->setFormat(self::COMPRESSED_IMAGE_EXTENSION);
            // (1 = lento/óptimo, 9 = rápido/pesado)
            $optimized->setOption(self::COMPRESSED_IMAGE_EXTENSION . ":speed", "6");
            $optimized->setOption(self::COMPRESSED_IMAGE_EXTENSION . ":quality", "65");
            $optimized->writeImage($finalDestination);
            $optimized->clear();

            return;
        }

        move_uploaded_file($this->file['tmp_name'], $finalDestination);
    }

    // ============================================================================
    // MARK: UPDATE FILENAME
    // ============================================================================
    protected function updateFileName(string $column, string $table, string $primaryKey, string $primaryKeyValue, FileTypes $filetype): ?string
    {
        if ($this->file === null) {
            if ($this->deleteCheckbox === true) {
                return null;
            }
            $fileUrl = $this->getFileUrl($column, $table, $primaryKey, $primaryKeyValue);
            return $fileUrl;
        }

        $this->setUniqueFilename($this->file['name'], $filetype);

        $imagePath = self::COMMON_FILE_PATH . $this->extraDirectories . $this->uniqueFilename;

        return $imagePath;
    }

    // ============================================================================
    // MARK: UPDATE FILE
    // ============================================================================
    protected function updateFile(?string $filePath, FileTypes $filetype): void
    {
        if ($this->file === null) {
            if ($this->deleteCheckbox === true) {
                $this->deleteFile($filePath);
                return;
            }
            return;
        }

        $this->deleteFile($filePath);
        $this->uploadFile($filetype);
    }

    // ============================================================================
    // MARK: DELETE FILE
    // ============================================================================
    protected function deleteFile(?string $filePath): void
    {
        if ($filePath !== null) {
            unlink($_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH . $filePath);
        }
    }

    // ============================================================================
    // MARK: DELETE ALL FILES
    // ============================================================================
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

    // ============================================================================
    // MARK: GET FILE URL
    // ============================================================================
    protected function getFileUrl(string $column, string $table, string $primaryKey, string $primaryKeyValue): string|null|false
    {
        $query =
            "SELECT $column
            FROM $table
            WHERE $primaryKey = ?";

        $params = [['s', $primaryKeyValue]];

        $fileUrl = $this->executeQuery($query, $params, SqlReturn::FetchColumn);

        return $fileUrl;
    }

    // ============================================================================
    // MARK: OBTAIN MIME
    // ============================================================================
    protected function obtainMime(string $filename): string|false
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($filename);

        return $mime;
    }

    // ============================================================================
    // MARK: SHOW FILE
    // ============================================================================
    protected function showFile(): void
    {
        $base = realpath($_SERVER['DOCUMENT_ROOT'] . self::COMMON_FILE_PATH);

        if ($base === false) {
            $this->integrityErrorSetup(500, "Tenemos problemas técnicos para encontrar esa ruta");
        }

        // Normalizar la ruta solicitada
        $rutaSolicitada = realpath($base . '/' . $_GET['f']);

        // Validación: el archivo debe estar dentro de /private
        if (!$rutaSolicitada || !str_starts_with($rutaSolicitada, $base)) {
            $this->integrityErrorSetup(403, "Acceso no permitido.");
        }

        if (!is_file($rutaSolicitada)) {
            $this->integrityErrorSetup(403, "No puedes acceder a directorios.");
        }

        $mime = $this->obtainMime($rutaSolicitada);

        if (
            !str_starts_with($mime, 'image/') &&
            !str_starts_with($mime, 'audio/') &&
            !str_starts_with($mime, 'video/')
        ) {
            $this->integrityErrorSetup(403, "Tipo de archivo no permitido.");
        }

        $mtime = filemtime($rutaSolicitada);
        $size  = filesize($rutaSolicitada);

        // Enviar archivo
        header("Content-Type: $mime");
        header("Content-Length: $size");
        header("X-Content-Type-Options: nosniff");

        header("Cache-Control: public, max-age=31536000, immutable");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $mtime) . " GMT");

        header("ETag: \"" . md5($rutaSolicitada . $mtime . $size) . "\"");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 31536000) . " GMT");

        header("Accept-Ranges: bytes");

        readfile($rutaSolicitada);
        exit;
    }
}
