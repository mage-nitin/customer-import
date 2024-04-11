<?php
declare(strict_types=1);

namespace MageNit\CustomersImport\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MageNit\CustomersImport\Model\CustomerImport;

/**
 * Console command class to get command line inputs and perform import action.
 */
class CustomersCreateCommand extends Command
{
    /** Command Name .*/
    private const NAME = 'ng:customers:import';
    /** Option to pass file path, json or csv.*/
    private const SOURCE_FILE = 'profile-file';

    /** @var CustomerImport $customerImport */
    private CustomerImport $customerImport;

    /**
     * Class Constructor.
     *
     * @param CustomerImport $customerImport
     */
    public function __construct(CustomerImport $customerImport)
    {
        $this->customerImport = $customerImport;
        parent::__construct(self::NAME);
    }

    /**
     * Config Function
     *
     * @return void
     */
    protected function configure(): void
    {
        $options = [
            new InputOption(
                self::SOURCE_FILE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Source file for import, either json or csv'
            )
        ];
        $this->setName(self::NAME);
        $this->setDescription('This commands imports customers from csv file either json file.');
        $this->setDefinition($options);

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;
        try {
            $output->writeln('<info>Import Process Start...</info>');
            // Setting allowed file extension Default.
            $this->customerImport->setAllowedFileExtension();
            $file = $input->getOption(self::SOURCE_FILE);
            $totalCount = $this->customerImport->startImport($file);
            if ($totalCount) {
                $output->writeln('<info>Total Customer Created : </info>' . $totalCount);
            }
            $output->writeln('<info>Import Process End...</info>');
        } catch (LocalizedException $e) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $e->getMessage()
            ));
            $exitCode = 1;
        }

        return $exitCode;
    }
}
