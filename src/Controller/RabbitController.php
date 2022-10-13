<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Command\KafkaCommand;
use App\Controller\Command\RabbitCommand;
use Bunny\Client;
use Bunny\Message;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private string $rabbitHost,
        private int $rabbitPort,
        private string $rabbitUser,
        private string $rabbitPassword,
        private string $rabbitVhost
    ) {
    }

    #[Route('/', name: 'rabbit.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return new JsonResponse(['prezentuje' => 'Andrzej Piotrowski - andrzej.pitorowski@eobuwie.com.pl']);
    }

    #[Route('/rabbit/publish-messenger', name: 'rabbit.publish_messenger', methods: ['GET'])]
    public function publishMessenger(Request $request): Response
    {
        $this->commandBus->dispatch(new RabbitCommand("Rabbit hello world", new DateTime()));

        return new JsonResponse('The Message was send to rabbit');
    }

    #[Route('/rabbit/publish/bunny', name: 'rabbit.publish_bunny', methods: ['GET'])]
    public function publishWithBunny(Request $request): Response
    {
        $bunny = new Client([
            'host' => $this->rabbitHost,
            'vhost' => $this->rabbitVhost,
            'user' => $this->rabbitUser,
            'password' => $this->rabbitPassword,
        ]);
        $bunny->connect();

        $channel = $bunny->channel();

        $channel->queueDeclare('test.bunny');
        $channel->queueDeclare('test.bunny.durable',false, true);

        $channel->publish('{msg: "not durable"}', [], 'amq.direct', 'test.bunny');
        $channel->publish('{test: "durable"}', [], '', 'test.bunny.durable');

        $bunny->disconnect();


        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/rabbit/publish/native', name: 'rabbit.publish_native', methods: ['GET'])]
    public function publishWithNative(Request $request): Response
    {
        $connection = new AMQPStreamConnection(
            $this->rabbitHost,
            $this->rabbitPort,
            $this->rabbitUser,
            $this->rabbitPassword
        );
        $channel = $connection->channel();

        $channel->queue_declare('test.native', false, false, false, false);
        $channel->queue_declare('test.native.durable', false, true, false, false);

        $msg = new AMQPMessage('{msg: "not durable"}');
        $channel->basic_publish($msg, '', 'test.native');

        $msg = new AMQPMessage('{msg: "durable"}');
        $channel->basic_publish($msg, '', 'test.native.durable');

        $channel->close();

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/rabbit/publish/death-letter', name: 'rabbit.publish', methods: ['GET'])]
    public function publishToDeathLetterInit(): JsonResponse
    {
        $bunny = new Client([
            'host' => $this->rabbitHost,
            'vhost' => $this->rabbitVhost,
            'user' => $this->rabbitUser,
            'password' => $this->rabbitPassword,
        ]);
        $bunny->connect();

        $channel = $bunny->channel();

        $channel->publish('{msg: "New message"}', [], '', 'test.bunny');

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/rabbit/consume/single-message', name: 'rabbit.consume_single_message', methods: ['GET'])]
    public function consumeSingleMessage(): JsonResponse
    {
        $bunny = new Client([
            'host' => $this->rabbitHost,
            'vhost' => $this->rabbitVhost,
            'user' => $this->rabbitUser,
            'password' => $this->rabbitPassword,
        ]);
        $bunny->connect();

        $channel = $bunny->channel();
        /** @var Message $message */
        $message = $channel->get('test.bunny');

        if (null === $message) {
            return new JsonResponse(['status' => 'Not found message to get']);
        }

        $headers = $message->headers;
        $content = $message->content;

//        $channel->ack($message);
//        $channel->reject($message, false);
//        $channel->nack($message, false, false);

        return new JsonResponse(['content' => $content, 'headers' => $headers]);
    }

    #[Route('/rabbit/death-letter-init', name: 'rabbit.death_letter_init', methods: ['GET'])]
    public function deathLetterInit(): JsonResponse
    {
        $bunny = new Client([
            'host' => $this->rabbitHost,
            'vhost' => $this->rabbitVhost,
            'user' => $this->rabbitUser,
            'password' => $this->rabbitPassword,
        ]);
        $bunny->connect();

        $channel = $bunny->channel();
        /** @var Message $message */
        $channel->queueDeclare('test.bunny.retry', false, true, false, false, false, [
            'x-dead-letter-exchange' => '',
            'x-dead-letter-routing-key' => 'test.bunny',
            'x-message-ttl' => 10000
        ]);

        $channel->queueDeclare('test.bunny.fail', false, true, false, false, false);

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/rabbit/publish/death-letter', name: 'rabbit.publish_death_letter', methods: ['GET'])]
    public function publishToDeathLetterRetry(): JsonResponse
    {
        $bunny = new Client([
            'host' => $this->rabbitHost,
            'vhost' => $this->rabbitVhost,
            'user' => $this->rabbitUser,
            'password' => $this->rabbitPassword,
        ]);
        $bunny->connect();
        $channel = $bunny->channel();

        /** @var Message $message */
        $message = $channel->get('test.bunny');

        if (null === $message) {
            return new JsonResponse(['status' => 'Not found message to death letter']);
        }

        $channel->nack($message, false, false);

        if ($message->getHeader('x-death') && $message->getHeader('x-death')[0]['count'] >= 2) {
            $channel->publish($message->content, $message->headers, '', 'test.bunny.fail');
        } else {
            $channel->publish($message->content, $message->headers, '', 'test.bunny.retry');
        }

        $channel->close();
        $bunny->disconnect();

        return new JsonResponse(['status' => 'success', 'content' => $message->content, 'headers' => $message->headers]);
    }
}