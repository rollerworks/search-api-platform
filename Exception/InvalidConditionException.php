<?php

declare(strict_types=1);

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\ApiPlatform\Exception;

use ApiPlatform\JsonSchema\SchemaFactory;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Error as Operation;
use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use ApiPlatform\Metadata\Exception\RuntimeException;
use Rollerworks\Component\Search\ConditionErrorMessage;
use Rollerworks\Component\Search\ErrorList;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as SymfonyHttpExceptionInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\WebLink\Link;

#[ErrorResource(
    shortName: 'SearchViolation',
    description: 'Unprocessable condition',
    operations: [
        new Operation(
            outputFormats: ['jsonld' => ['application/problem+json', 'application/ld+json']],
            links: [new Link(rel: 'http://www.w3.org/ns/json-ld#error', href: 'http://www.w3.org/ns/hydra/error')],
            errors: [],
            normalizationContext: [
                SchemaFactory::OPENAPI_DEFINITION_NAME => '',
                'groups' => ['jsonld'],
                'skip_null_values' => true,
                'ignored_attributes' => ['trace', 'file', 'line', 'code', 'message', 'traceAsString', 'previous'],
            ],
            name: '_api_errors_hydra',
        ),
    ],
    outputFormats: [
        'json' => ['application/problem+json', 'application/json'],
    ],
    status: Response::HTTP_BAD_REQUEST,
    openapi: false,
    graphQlOperations: []
)]
#[ApiProperty(property: 'traceAsString', hydra: false)]
#[ApiProperty(property: 'string', hydra: false)]
class InvalidConditionException extends RuntimeException implements ProblemExceptionInterface, HttpExceptionInterface, SymfonyHttpExceptionInterface
{
    private int $status = 422;

    /** @var ConditionErrorMessage[] */
    private readonly array $errors;

    #[Groups(['jsonld', 'json', 'jsonapi'])]
    #[ApiProperty(writable: false, initializable: false)]
    private readonly string $detail;

    public function __construct(ErrorList $error, int $code = 0, ?\Throwable $previous = null)
    {
        $this->errors = $error->getArrayCopy();

        parent::__construct($messages = $this->__toString(), $code, $previous);
        $this->detail = $messages;
    }

    #[Groups(['jsonld'])]
    #[ApiProperty(writable: false, initializable: false)]
    public function getDescription(): string
    {
        return $this->detail;
    }

    #[Groups(['jsonld', 'json', 'jsonapi'])]
    #[ApiProperty(writable: false, initializable: false)]
    public function getType(): string
    {
        return 'https://tools.ietf.org/html/rfc2616#section-10';
    }

    #[Groups(['jsonld', 'json', 'jsonapi'])]
    #[ApiProperty(writable: false, initializable: false)]
    public function getTitle(): ?string
    {
        return 'The search condition is invalid';
    }

    #[Groups(['jsonld', 'json', 'jsonapi'])]
    #[ApiProperty(writable: false, initializable: false)]
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    #[Groups(['jsonld', 'json', 'jsonapi'])]
    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getInstance(): ?string
    {
        return null;
    }

    #[SerializedName('violations')] // XXX Rename to errors in v3.0
    #[Groups(['json', 'jsonld'])]
    #[ApiProperty(
        jsonldContext: ['@type' => 'SearchViolationList'],
        schema: [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'propertyPath' => ['type' => 'string', 'description' => 'The path of the error'],
                    'message' => ['type' => 'string', 'description' => 'The message associated with the error'],
                ],
            ],
        ]
    )]
    public function getSearchViolationList(): array
    {
        return $this->errors;
    }

    public function __toString(): string
    {
        $message = '';

        foreach ($this->errors as $error) {
            if ($error->path !== '') {
                $message .= "{$error->path}: ";
            }

            $message .= $error->message;
        }

        return $message;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return [];
    }
}
