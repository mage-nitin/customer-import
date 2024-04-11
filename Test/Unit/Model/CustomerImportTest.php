<?php

declare(strict_types=1);

namespace MageNit\CustomersImport\Test\Unit\Model;

use MageNit\CustomersImport\Model\CustomerImport;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\File\Csv;
use MageNit\CustomersImport\Api\Data\CustomerImportInputInterface;
use MageNit\CustomersImport\Model\Validator\Validation;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Serialize\Serializer\Json;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerImportTest extends TestCase
{
    public const ALLOWED_FILE_EXTENSION = [
        "application/json",
        "text/csv",
    ];

    /** @var CustomerImport */
    protected CustomerImport $customerModel;

    /** @var Json|MockObject $jsonSerializer */
    private Json|MockObject $jsonSerializer;

    /** @var FileDriver|MockObject $fileDriver */
    private FileDriver|MockObject $fileDriver;

    /** @var CustomerInterfaceFactory|MockObject $customerInterfaceFactory */
    private CustomerInterfaceFactory|MockObject $customerInterfaceFactory;

    /** @var Validation|MockObject $validation */
    private Validation|MockObject $validation;

    /** @var CustomerImportInputInterface|MockObject $customerImportInputInterface */
    private CustomerImportInputInterface|MockObject $customerImportInputInterface;

    /** @var Csv|MockObject $csvProcessor */
    private Csv|MockObject $csvProcessor;

    /**
     * @var CustomerRepositoryInterface|MockObject $customerRepository
     */
    private CustomerRepositoryInterface|MockObject $customerRepository;
    
    protected function setUp(): void
    {
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->csvProcessor = $this->createMock(Csv::class);
        $this->customerImportInputInterface = $this->getMockForAbstractClass(
            CustomerImportInputInterface::class
        );
        $this->validation = $this->createMock(Validation::class);
        $this->customerInterfaceFactory = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->fileDriver = $this->createMock(FileDriver::class);
        $this->jsonSerializer = $this->createMock(Json::class);
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->customerModel = $objectManagerHelper->getObject(
            CustomerImport::class,
            [
                'customerRepository' => $this->customerRepository,
                'csvProcessor' => $this->csvProcessor,
                'customerImportInputInterface' => $this->customerImportInputInterface,
                'validation' => $this->validation,
                'customerInterfaceFactory' => $this->customerInterfaceFactory,
                'fileDriver' => $this->fileDriver,
                'jsonSerializer' => $this->jsonSerializer
            ]
        );
    }

    /**
     * @param $expectedResult
     * @dataProvider getAllowedFileExtensionDataProvider
     */
    public function testGetAllowedFileExtension($allowedFile, $expectedResult)
    {
        $this->customerModel->setAllowedFileExtension($allowedFile);
        $this->assertEquals($expectedResult, $this->customerModel->getAllowedFileExtension());
    }

    /**
     * @return array
     */
    public function getAllowedFileExtensionDataProvider(): array
    {
        return [
            [self::ALLOWED_FILE_EXTENSION, 'expectedResult' => self::ALLOWED_FILE_EXTENSION],
            [[], 'expectedResult' => []]
        ];
    }
}
