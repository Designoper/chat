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
    private const string COMPRESSED_IMAGE_EXTENSION = 'webp';
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
            if (pathinfo($originalFilename, PATHINFO_EXTENSION) === 'gif') {
                $extension = 'webp';
            } else $extension = self::COMPRESSED_IMAGE_EXTENSION;
        } else $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

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

        // Aseguramos extensión webp
        // $filenameWithWebp = preg_replace('/\.[^.]+$/', '.webp', $this->uniqueFilename);
        $finalDestination = $folderDestination . $this->uniqueFilename;

        // RUTA TEMPORAL: Evita que el archivo final sea leído mientras Imagick procesa los fotogramas
        $tempDestination = $finalDestination . '.tmp';

        if ($filetype === FileTypes::Image) {
            $imagick = new Imagick($this->file['tmp_name']);
            $ancho_deseado = 800;
            $extension_destino = 'webp';

            if ($imagick->getNumberImages() > 1) {
                $optimized = $imagick->coalesceImages();
                $imagick->clear();

                foreach ($optimized as $frame) {
                    $ancho_original = $frame->getImageWidth();
                    if ($ancho_original > $ancho_deseado) {
                        $frame->scaleImage($ancho_deseado, 0);
                    }
                    $frame->stripImage();
                    $frame->setFormat($extension_destino);
                    $frame->setOption('webp:lossless', 'false');
                    $frame->setImageCompressionQuality(65);
                }

                $optimized->setFormat($extension_destino);
                // Guardamos en el archivo temporal primero
                $optimized->writeImages($tempDestination, true);
                $optimized->clear();
            } else {
                $ancho_original = $imagick->getImageWidth();
                if ($ancho_original > $ancho_deseado) {
                    $imagick->scaleImage($ancho_deseado, 0);
                }
                $imagick->stripImage();
                $imagick->setFormat($extension_destino);
                $imagick->setOption('webp:lossless', 'false');
                $imagick->setImageCompressionQuality(65);
                // Guardamos en el archivo temporal primero
                $imagick->writeImage($tempDestination);
                $imagick->clear();
            }

            // OPERACIÓN ATÓMICA: Ahora que el archivo está completo, lo renombramos al destino final
            if (file_exists($tempDestination)) {
                rename($tempDestination, $finalDestination);
            }
        } else {
            // Para archivos que no son imágenes, también procesamos mediante temporal para mantener consistencia
            if (move_uploaded_file($this->file['tmp_name'], $tempDestination)) {
                rename($tempDestination, $finalDestination);
            }
        }

        // --- BUCLE DE RETENCIÓN DE FLUJO ---
        // Detiene el código hasta que el archivo final esté 100% escrito y estable en disco
        $intentos = 0;
        $maxIntentos = 30; // Tiempo límite de espera: 30 segundos
        $tamanoAnterior = 0;

        while ($intentos < $maxIntentos) {
            // Forzamos al sistema operativo a ignorar su memoria caché de estado de archivos
            clearstatcache(true, $finalDestination);

            if (file_exists($finalDestination)) {
                $tamanoActual = filesize($finalDestination);

                // Si mide más de 0 bytes y el tamaño no ha cambiado respecto al segundo anterior, está listo
                if ($tamanoActual > 0 && $tamanoActual === $tamanoAnterior) {
                    break;
                }

                $tamanoAnterior = $tamanoActual;
            }

            sleep(1); // Detiene el script por 1 segundo antes de volver a verificar
            $intentos++;
        }

        if ($intentos >= $maxIntentos) {
            throw new \RuntimeException("Error: El archivo tardó demasiado en confirmarse en el disco.");
        }

        // El código continuará a partir de aquí de forma completamente síncrona y segura
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

        // Validación de seguridad física de la ruta
        if (!$rutaSolicitada || !str_starts_with($rutaSolicitada, $base)) {
            $this->integrityErrorSetup(403, "Acceso no permitido.");
        }

        if (!is_file($rutaSolicitada)) {
            $this->integrityErrorSetup(403, "No puedes acceder a directorios.");
        }

        // Detectamos la extensión real del archivo solicitado en el disco
        $extension = strtolower(pathinfo($rutaSolicitada, PATHINFO_EXTENSION));

        // Si es un WebP procesado por nosotros, forzamos su MIME para evitar fallos de lectura interna de la librería finfo
        if ($extension === 'webp') {
            $mime = 'image/webp';
        } else {
            $mime = $this->obtainMime($rutaSolicitada);
        }

        // Validación final de seguridad de tipos de archivo permitidos
        if (
            !str_starts_with($mime, 'image/') &&
            !str_starts_with($mime, 'audio/') &&
            !str_starts_with($mime, 'video/')
        ) {
            $this->integrityErrorSetup(403, "Tipo de archivo no permitido.");
        }

        $mtime = filemtime($rutaSolicitada);
        $size  = filesize($rutaSolicitada);

        // Enviar archivo de forma limpia
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
