<?php
declare(strict_types=1);
namespace App\Command;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class RabbitConsumer extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('consumer:rabbit')
            ->setDescription('Consume messages')
            ->addOption('run-time', 'rt', InputOption::VALUE_OPTIONAL, 'Time describe how long queue should be consumed before end.', 1);
    }

    public function __construct(private string $rabbitHost, private int $rabbitPort, private string $rabbitUser, private string $rabbitPassword, private string $rabbitVhost)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $runTime = $input->getOption('run-time');

        $output->writeln([
            '<info>Rabbit Consumer</info>',
            '============',
            '',
        ]);

        $bunny = new Client([
            'host'      => $this->rabbitHost,
            'vhost'     => $this->rabbitVhost,
            'user'      => $this->rabbitUser,
            'password'  => $this->rabbitPassword,
        ]);
        $bunny->connect();

        $channel = $bunny->channel();
        $channel->qos(0, 1);

        $channel->consume(
            function (Message $message, Channel $channel, Client $client) use ($output){
                $success = true; // Handle your message here
                sleep(1);
                if ($success) {
                    $output->writeln('Command successfully consumed');

                    $channel->ack($message); // Acknowledge message
                    return;
                }

                $output->writeln('Message was consumed with error');

                $channel->nack($message); // Mark message fail, message will be redelivered
            },
            'test.bunny'
        );
        $bunny->run($runTime ?? 1);

        $output->writeln([
            '',
            '============',
            '<info>Finish</info>',
        ]);

        return Command::SUCCESS;
    }
}