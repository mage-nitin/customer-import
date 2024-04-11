<?php

namespace MageNit\CustomersImport\Api\Data;

/**
 * Customer import allowed profiles interface.
 */
interface CustomerImportInputInterface
{
    public const ALLOWED_FILES = 'allowed_files';

    /**
     * Get mime type for allowed file for import.
     *
     * @return string[]
     */
    public function getAllowedFileExtensions();

    /**
     * Set mime type for allowed file for import.
     *
     * @param string[] $allowedFileExtensions
     * @return string[]
     */
    public function setAllowedFileExtensions(array $allowedFileExtensions);
}
