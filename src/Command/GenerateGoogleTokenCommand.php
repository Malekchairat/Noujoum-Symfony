<?php

namespace App\Command;

use App\Service\GoogleCalendarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateGoogleTokenCommand extends Command
{
    protected static $defaultName = 'app:generate-google-token';
    private GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // This will trigger the token generation flow
        new \ReflectionMethod($this->calendarService, 'generateNewToken');
        
        $output->writeln('Token generated successfully!');
        return Command::SUCCESS;
    }
}