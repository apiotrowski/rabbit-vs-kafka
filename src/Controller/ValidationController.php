<?php
declare(strict_types=1);
namespace App\Controller;

use JsonSchema\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ValidationController extends AbstractController
{
    #[Route('/validate-message', name: 'validation.validate-message', methods: ['GET'])]
    public function validateMessage(): Response
    {
        //$message = json_decode('{"jsonrpc": "2.0", "method": "CreatedProduct", "params": {"name": "Buty", "createdAt": "2018-08-28"}, "id": "123e4567-e89b-12d3-a456-426655440000"}');
        $message = json_decode('{"jsonrpc": "2.0", "method": "Created", "params": {"name": "Buty", "createdAt": "201808-28"}, "id": "123e4567-e89b-12d3-a456-426655440000"}');

        $validator = new Validator();

        $schemaPath = $this->getParameter('kernel.project_dir') . '/config/schema/schema.json';

        $validator->validate($message, json_decode(file_get_contents($schemaPath)));

        if (true === $validator->isValid()) {
            $result = "JSON Valid";
        } else {
            $result = "Invalid JSON. Violations:";
            foreach ($validator->getErrors() as $error) {
                $result .= sprintf("[%s] %s,", $error['property'], $error['message']);
            }
        }

        return new JsonResponse(['result' => $result]);
    }
}