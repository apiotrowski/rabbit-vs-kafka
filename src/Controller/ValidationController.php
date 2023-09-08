<?php
declare(strict_types=1);
namespace App\Controller;

use Gaufrette\Filesystem;
use JsonSchema\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ValidationController extends AbstractController
{
    public function __construct(public readonly Filesystem $schemasFilesystem)
    {
    }

    #[Route('/validate', name: 'validation.validate-message', methods: ['POST'])]
    public function validate(Request $request): Response
    {
        $body = json_decode($request->getContent(), false);

        $schema = json_decode(
            $this->schemasFilesystem->get('schema.json')->getContent()
        );

        $validator = new Validator();
        $validator->validate($body, $schema);

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