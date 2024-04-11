<?php
namespace MageNit\CustomersImport\Model\Data;

use MageNit\CustomersImport\Api\Data\CustomerImportInputInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class to define interface functions, which allow file type to import
 */
class CustomerImportInput extends AbstractSimpleObject implements CustomerImportInputInterface
{
    /**
     * Get Allowed file extension
     *
     * @return array|string[]
     */
    public function getAllowedFileExtensions(): array
    {
        return $this->_get(self::ALLOWED_FILES);
    }

    /**
     * Set Allowed file extension
     *
     * @param array $allowedFileExtensions
     * @return $this
     */
    public function setAllowedFileExtensions(array $allowedFileExtensions): static
    {
        return $this->setData(self::ALLOWED_FILES, $allowedFileExtensions);
    }
}
