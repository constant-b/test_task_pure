<?php


class UploadedFile
{

    const ALLOWED_EXTENSIONS = ["pdf", "bmp", "jpeg", "jpg", "gif", "png", "doc", "docx", "pptx", "ppt", "xls", "xlsx"];

    /**
     * @var null|string $name
     * Original file name.
     *
     */
    public ?string $name;

    /**
     * @var string|null $type
     * Uploaded file type.
     */
    public ?string $type;

    /**
     * @var string|null $size
     * Size of uploaded file.
     */
    public ?string $size;

    /**
     * @var string|null $tempName
     * Temporary uploaded file name.
     */
    public ?string $tempName;

    /**
     * @var int|null $error
     * Contains file error code.
     * Codes and explaining: https://www.php.net/manual/en/features.file-upload.errors.php
     */
    public ?int $error;

    /**
     * @var bool $hasErrors
     * True if file contains errors.
     */
    public bool $hasErrors = false;

    /**
     * @var array|string|string[] $extension
     * File's extension.
     */
    public $extension;

    /**
     * @var array|null $files
     * Contains all uploaded files.
     */
    private static ?array $files;


    /**
     * Saver constructor.
     *
     * @param array $info - Array with data about single file from $_FILES global variable.
     */
    public function __construct(array $info)
    {
        foreach ($info as $name => $value) {
            if (!property_exists($this, $name)) continue;
            $this->$name = $value;
        }

        $this->checkFile();

        $this->extension = pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * @param string $path Includes path and file name
     *
     * @return bool
     */
    public function save(string $path): bool
    {
        if (!$this->isAllowedExtension() || $this->hasErrors) return false;

        $dirname = pathinfo($path)["dirname"];

        if (!is_dir($dirname)) mkdir($dirname, 0755, true);

        return move_uploaded_file($this->tempName, $path);
    }

    /**
     * Checks file extension.
     *
     * @return bool
     */
    private function isAllowedExtension(): bool
    {
        return in_array($this->getExtension(), self::ALLOWED_EXTENSIONS);
    }

    /**
     * Check file error.
     */
    private function checkFile(): void
    {
        if (!is_uploaded_file($this->tempName) || ((int)$this->error !== UPLOAD_ERR_OK)) $this->hasErrors = true;
    }

    /**
     * Load files from $_FILES to static::$files
     */
    private static function loadFiles(): void
    {
        foreach ($_FILES as $name => $info) {
            static::$files[$name] = [
                'name'     => $info['name'],
                'type'     => $info['type'],
                'size'     => $info['size'],
                'tempName' => $info['tmp_name'],
                'error'    => $info['error']
            ];
        }
    }

    /**
     * Get file, load it to Saver and return instance you can work with.
     *
     * @param string $inputName - Input file name.
     *
     * @return UploadedFile|null
     */
    public static function getFile(string $inputName): ?UploadedFile
    {
        self::loadFiles();

        if (isset(static::$files[$inputName])) {
            return new self(static::$files[$inputName]);
        }

        return null;
    }

    /**
     * Generates random file name.
     *
     * @param string $extension File's extension
     *
     * @return string
     */
    public static function generateRandomName(string $extension): string
    {
        return md5(microtime() . rand(0, 9000)) . "." . $extension;
    }

    /**
     * @return array|string|string[]
     */
    public function getExtension()
    {
        return $this->extension;
    }


}