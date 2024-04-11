<?php
namespace MageNit\CustomersImport\Model\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Driver\File\Mime;

/**
 * Validation Class to validate input file from console
 */
class Validation
{
    /** @var File $fileInterface */
    private File $fileInterface;

    /** @var Mime $fileMime */
    private Mime $fileMime;

    /**
     * Validation Constructor
     *
     * @param File $fileInterface
     * @param Mime $fileMime
     */
    public function __construct(File $fileInterface, Mime $fileMime)
    {
        $this->fileInterface  = $fileInterface;
        $this->fileMime       = $fileMime;
    }

    /**
     * Validation File
     *
     * @param string $filePath
     * @param array $allowedFileTypes
     * @return bool
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function validateFile(string $filePath, array $allowedFileTypes): bool
    {
        if (empty($filePath)) {
            throw new LocalizedException(__("Please Provide File path"));
        }
        if (!$this->fileInterface->isFile($filePath)) {
            throw new LocalizedException(__("Not a File!"));
        }
        if (!$this->fileInterface->isExists($filePath)) {
            throw new LocalizedException(__("File does not exist!"));
        }
        $mimeType = $this->getFileMimeType($filePath);
        if (!empty($mimeType) && !in_array($mimeType, $allowedFileTypes)) {
            throw new LocalizedException(__("File type not allowed to import!"));
        }
        return true;
    }

    /**
     * Get Mimetype of File
     *
     * @param string $filePath
     * @return string
     * @throws FileSystemException
     */
    public function getFileMimeType(string $filePath): string
    {
        return $this->fileMime->getMimeType($filePath);
    }
}
