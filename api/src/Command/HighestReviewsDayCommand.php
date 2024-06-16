<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:highest-reviews-day',
    description: 'Add a short description for your command',
)]
class HighestReviewsDayCommand extends Command
{
    protected static $defaultName = 'app:highest-reviews-day';
    protected static $defaultDescription = 'Displays the day with the highest number of reviews published';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription)
        ->addOption('month', 'm', InputOption::VALUE_NONE, 'Display the month instead of the day');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $this->entityManager->getConnection();

        $sql = "
            SELECT TO_CHAR(published_at, 'YYYY-MM') AS review_date, COUNT(*) AS review_count
            FROM review
            GROUP BY review_date
            ORDER BY review_count DESC, review_date DESC
            LIMIT 1;

            ";

        $stmt = $connection->prepare($sql);
        $result = $stmt->executeQuery()->fetchAssociative();

        if ($result) {
            if ($input->getOption('month')) {
                $io->success('The month with the highest number of reviews published is: ' . $result['review_month'] . ' with ' . $result['review_count'] . ' reviews.');
            } else {
                $io->success('The day with the highest number of reviews published is: ' . $result['review_day'] . ' with ' . $result['review_count'] . ' reviews.');
            }
        } else {
            $io->error('No reviews found.');
        }


        return Command::SUCCESS;
    }
}
