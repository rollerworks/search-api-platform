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
     * @return array{propertyPath: string, message: string}
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
        return \in_array($format, ['json', 'jsonproblem', 'jsonld'], true) && $data instanceof ConditionErrorMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        if (\in_array($format, ['json', 'jsonproblem', 'jsonld'], true)) {
            return [
                ConditionErrorMessage::class => true,
            ];
        }

        return [];
    }
}
