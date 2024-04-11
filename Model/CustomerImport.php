<?php
declare(strict_types=1);

namespace MageNit\CustomersImport\Model;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\File\Csv;
use MageNit\CustomersImport\Api\Data\CustomerImportInputInterface;
use MageNit\CustomersImport\Model\Validator\Validation;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Serialize\Serializer\Json;

class CustomerImport
{
    public const ALLOWED_FILE_EXTENSION = [
        "application/json",
        "text/csv",
    ];

    public const PREDEFINED_CSV_HEADER = [
        "firstname" =>"fname",
        "lastname" => "lname",
        "email" => "emailaddress"
    ];

    /** @var int $totalCustomersCreated */
    private int $totalCustomersCreated = 0;

    /** @var array $csvFileHeader */
    private array $csvFileHeader;

    /** @var int $customerCount */
    private int $customerCount;

    /** @var Json $jsonSerializer */
    private Json $jsonSerializer;

    /** @var FileDriver $fileDriver */
    private FileDriver $fileDriver;

    /** @var CustomerInterfaceFactory $customerInterfaceFactory */
    private CustomerInterfaceFactory $customerInterfaceFactory;

    /** @var Validation $validation */
    private Validation $validation;

    /** @var CustomerImportInputInterface $customerImportInputInterface */
    private CustomerImportInputInterface $customerImportInputInterface;

    /** @var Csv $csvProcessor */
    private Csv $csvProcessor;

    /**
     * @var CustomerRepositoryInterface $customerRepository
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Csv $csvProcessor
     * @param CustomerImportInputInterface $customerImportInputInterface
     * @param Validation $validation
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param FileDriver $fileDriver
     * @param Json $jsonSerializer
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Csv $csvProcessor,
        CustomerImportInputInterface $customerImportInputInterface,
        Validation $validation,
        CustomerInterfaceFactory $customerInterfaceFactory,
        FileDriver $fileDriver,
        Json $jsonSerializer
    ) {
        $this->customerRepository = $customerRepository;
        $this->csvProcessor = $csvProcessor;
        $this->customerImportInputInterface = $customerImportInputInterface;
        $this->validation = $validation;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->fileDriver = $fileDriver;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Start point of this class.
     *
     * @param string $file
     * @return string|int
     * @throws LocalizedException
     */
    public function startImport(string $file): string|int
    {
        try {
            $allowedFileTypes = $this->getAllowedFileExtension();
            $this->validation->validateFile($file, $allowedFileTypes);
            $preparedDataForProcessing = $this->prepareDataFromFile($file);
            if (!empty($preparedDataForProcessing)) {
                $this->processDataImport($preparedDataForProcessing);
            } else {
                throw new LocalizedException(__("No Record Found To Create!"));
            }
        } catch (LocalizedException $e) {
            //Handle the Local Exception if any
            throw new LocalizedException(__($e->getMessage()));
        } catch (Exception $e) {
            //Handle the Other Exception if any
            throw new LocalizedException(__($e->getMessage()));
        }
        return $this->totalCustomersCreated;
    }

    /**
     * This function to set array of allowed file type, and can be extended for override/enhance import
     *
     * @param array $fileExtension
     * @return void
     */
    public function setAllowedFileExtension(array $fileExtension = self::ALLOWED_FILE_EXTENSION): void
    {
        $this->customerImportInputInterface->setAllowedFileExtensions($fileExtension);
    }

    /**
     * This function is to process records from csv.
     *
     * @param CustomerInterface[] $customerInterfaceArray
     * @return void
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function processDataImport(array $customerInterfaceArray): void
    {
        foreach ($customerInterfaceArray as $customerData) {
            /** CustomerInterface $customerData*/
            if (!$this->isCustomerExist($customerData->getEmail())) {
                $this->customerRepository->save($customerData);
                $this->totalCustomersCreated = $this->totalCustomersCreated + 1;
            }
        }
    }

    /**
     * Function to prepare data from Csv and send for processing.
     *
     * @param string $file
     * @return CustomerInterface[]
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws Exception
     */
    protected function prepareDataFromFile(string $file): array
    {
        $fileType = $this->getFileMimeType($file);
        if ($fileType =="text/csv") {
            // Function call to prepare data for processing from Csv file.
            return $this->getAllDataFromCsv($file);
        } if ($fileType =="application/json") {
            return $this->getAllDataFromJsonFile($file);
        }
        return [];
    }

    /**
     * Function to prepare Data before processing by Customer Repository
     *
     * @param array $header
     * @param array $customerData
     * @return CustomerInterface[]|array
     */
    protected function prepareDataForProcessing(array $header, array $customerData): array
    {
        $allCustomerRecordsToCreate = [];
        foreach ($customerData as $customer) {
            $recordWithHeaderAsKey = array_combine($header, $customer);
            $customerInterface = $this->customerInterfaceFactory->create();
            foreach ($recordWithHeaderAsKey as $customerAttribute => $customerAttributeValue) {
                $customerInterface->setData($customerAttribute, $customerAttributeValue);
            }
            $allCustomerRecordsToCreate[] = $customerInterface;
        }
        return $allCustomerRecordsToCreate;
    }

    /**
     * Function to retrieve All data from csv
     *
     * @param string $file
     * @return CustomerInterface[]
     * @throws LocalizedException
     * @throws Exception
     */
    protected function getAllDataFromCsv(string $file): array
    {
        $customerData = $this->csvProcessor->getData($file);
        if (!empty($customerData)) {
            // retrieving header from Csv first row
            $this->csvFileHeader = reset($customerData);
            // unsetting header from Csv to have all data without first row.
            array_shift($customerData);
            $this->customerCount = count($customerData);
            $this->dataValidation(true);
            $headerToProcess = array_flip(self::PREDEFINED_CSV_HEADER);
            return $this->prepareDataForProcessing($headerToProcess, $customerData);
        } else {
            throw new LocalizedException(__("Empty File!"));
        }
    }

    /**
     * Function to fetch data from json file, passed in command line.
     *
     * @param string $file
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function getAllDataFromJsonFile(string $file): array
    {
        $jsonFileContent = $this->fileDriver->fileGetContents($file);
        if (!empty($jsonFileContent)) {
            $customerData = $this->jsonSerializer->unserialize($jsonFileContent);
            $this->customerCount = count($customerData);
            $this->dataValidation();
            $headerToProcess = array_flip(self::PREDEFINED_CSV_HEADER);
            return $this->prepareDataForProcessing($headerToProcess, $customerData);
        } else {
            throw new LocalizedException(__("Empty File!"));
        }
    }

    /**
     * Function to verify if customer exist.
     *
     * @param string $emailId
     * @return bool
     */
    protected function isCustomerExist(string $emailId): bool
    {
        $isCustomer = true;
        try {
            $this->customerRepository->get($emailId);
        } catch (Exception) {
            $isCustomer = false;
        }
        return $isCustomer;
    }

    /**
     * Get mime type of file
     *
     * @param string $file
     * @return string
     * @throws FileSystemException
     */
    private function getFileMimeType(string $file) : string
    {
        return $this->validation->getFileMimeType($file);
    }

    /**
     * Function to get allowed file type for import
     *
     * @return string[]
     */
    public function getAllowedFileExtension(): array
    {
        return $this->customerImportInputInterface->getAllowedFileExtensions();
    }

    /**
     * This function is to add all validation possible on csv file.
     *
     * @param bool|null $isCsvType
     * @return void
     * @throws LocalizedException
     */
    private function dataValidation($isCsvType = false): void
    {
        if ($isCsvType) {
            $this->validateCsv();
        }
        $this->validateDataCount();
    }

    /**
     * Compare Csv hear and predefined header, To check any discrepancy
     *
     * @throws LocalizedException
     */
    private function validateCsv(): void
    {
        if (array_diff($this->csvFileHeader, self::PREDEFINED_CSV_HEADER) != []) {
            throw new LocalizedException(__("Csv Columns are not Mapped with predefined header"));
        }
    }

    /**
     * Function to validateDataCount for customers
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateDataCount(): void
    {
        if ($this->customerCount < 2) {
            throw new LocalizedException(__("No Records Found to import!"));
        }
    }
}
