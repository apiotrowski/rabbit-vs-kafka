<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Command\KafkaCommand;
use DateTime;
use Enqueue\RdKafka\JsonSerializer;
use Interop\Queue\Message;
use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use RdKafka;

final class KafkaController extends AbstractController
{
    private const KAFKA_DSN = 'kafka:9092';

    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    #[Route('/kafka/publish-messenger', name: 'kafka.publish.messenger', methods: ['GET'])]
    public function publishMessenger(): JsonResponse
    {
        $this->commandBus->dispatch(new KafkaCommand("Kafka hello world", new DateTime()));

        return new JsonResponse('The Message was send to kafka');
    }

    #[Route('/kafka/publish', name: 'kafka.publish', methods: ['GET'])]
    public function publish(Request $request): JsonResponse
    {
        try {
            $connectionFactory = new RdKafkaConnectionFactory([
                'global' => [
                    'group.id' => uniqid('', true),
                    'metadata.broker.list' => self::KAFKA_DSN,
                ],
            ]);

            $context = $connectionFactory->createContext();

            $context->setSerializer(new JsonSerializer());

            // if you have enqueue/enqueue library installed you can use a factory to build context from DSN

            $messageBody = [
                "ordertime" => time(),
                "orderid" => random_int(0, 100),
                "itemid" => "Item_" . random_int(0, 100),
                "address" => [
                    "city" => "Mountain View",
                    "state" => "CA",
                    "zipcode" => 94041
                ]
            ];

            $message = $context->createMessage(json_encode($messageBody));

            $fooTopic = $context->createTopic('test2');

            $producer = $context->createProducer();

            $producer->send($fooTopic, $message);

            return new JsonResponse('The Message was send');
        } catch (\Throwable) {
            return new JsonResponse('Unsseccful send message to kafka');
        }
    }

    #[Route('/kafka/consume', name: 'kafka.consume', methods: ['GET'])]
    public function consume(Request $request): Response
    {
        $connectionFactory = new RdKafkaConnectionFactory([
            'global' => [
                'group.id' => 'moj2',
                'metadata.broker.list' => self::KAFKA_DSN,
                'enable.auto.commit' => 'false',
            ],
            'topic' => [
                'auto.offset.reset' => 'earliest', //earliest,beginning //none, earliest, latest
            ],
        ]);

        $context = $connectionFactory->createContext();

        $context->setSerializer(new JsonSerializer());

        $fooQueue = $context->createTopic('test2');

        $consumer = $context->createConsumer($fooQueue);

        $consumer->setCommitAsync(true);


        // Enable async commit to gain better performance (true by default since version 0.9.9).

        /** @var Message $message */
        $message = $consumer->receive(10000);

        if ($message) {
            $consumer->acknowledge($message);
        }

        return new JsonResponse($message ? json_decode($message->getBody(), true) : 'no message to read');
    }

    #[Route('/kafka/consume-raw', name: 'kafka.consume-raw', methods: ['GET'])]
    public function consumeRaw(Request $request)
    {
        $conf = new RdKafka\Conf();

        // Set a rebalance callback to log partition assignments (optional)
        $conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo "Revoke: ";
                    var_dump($partitions);
                    $kafka->assign(null);
                    break;

                default:
                    throw new \RuntimeException($err);
            }
        });

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $conf->set('group.id', 'moj2');

        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', self::KAFKA_DSN);

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'earliest': start from the beginning
        $conf->set('auto.offset.reset', 'earliest');

        // Emit EOF event when reaching the end of a partition
        $conf->set('enable.partition.eof', 'true');

        $consumer = new RdKafka\KafkaConsumer($conf);

        // Subscribe to topic 'test'
        $consumer->subscribe(['test2']);

        echo "Waiting for partition assignment... (make take some time when\n";
        echo "quickly re-joining the group after leaving it.)\n";

        while (true) {
            /** @var RdKafka\Message $message */
            $message = $consumer->consume(500);
            echo $message->err;
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    var_dump($message->payload);
                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                case RD_KAFKA_RESP_ERR__TRANSPORT:
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "TimeOut";
                    break;
                case RD_KAFKA_RESP_ERR_UNSUPPORTED_VERSION:
                    echo "Unsupported version";
                    break;
                default:
                    throw new \RuntimeException($message->errstr(), $message->err);
            }
        }
    }
}
