<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Controller\DebrickedApiController;


/**
 * SendNotificationCommand
 */
#[AsCommand(
    name: 'app:send-notification',
    description: 'command will trigger notification with email and slack channel for completed jobs',
)]

class SendNotificationCommand extends Command
{
    /**
     *  
     * @var DebrickedApiController
     */
    private DebrickedApiController $debrickApiController;
    public function __construct(DebrickedApiController $debrickApiController)
    {
        parent::__construct();
        $this->debrickApiController =$debrickApiController;
    }

    protected function configure(): void
    {
        $this
       ->setDescription('Verify pending jobs to scan and send notifications if vulnerabilities found from scan report');
    }

    /**
     * default execute call for current command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return int
     * 
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln('========Scanning File status and Notify user=======');
        $response=$this->debrickApiController->findPendingJobstoScan();
        if ($response) {
            $output->writeln('===========Process Completed==========');
        }

        return Command::SUCCESS;
    }
}
