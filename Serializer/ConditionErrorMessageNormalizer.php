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

namespace Rollerworks\Component\Search\ApiPlatform\Serializer;

use Rollerworks\Component\Search\ConditionErrorMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ConditionErrorMessageNormalizer implements NormalizerInterface
{
    /**
     * @param ConditionErrorMessage $data
     *
     * @return array{type: string, title: string, detail: string, violations: array<array{propertyPath: string, message: string, payload?: array<string, mixed>}>}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'propertyPath' => $data->path,
            'message' => $data->message,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return ($format === 'json' || $format === 'jsonproblem' || $format === 'jsonld') && $data instanceof ConditionErrorMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        if ($format === 'json' || $format === 'jsonproblem' || $format === 'jsonld') {
            return [
                ConditionErrorMessage::class => true,
            ];
        }

        return [];
    }
}
