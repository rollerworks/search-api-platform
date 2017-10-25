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

namespace Rollerworks\Component\Search\ApiPlatform\Elasticsearch\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Elastica\Client;
use Elastica\Document;
use Elastica\Search;
use Rollerworks\Component\Search\ApiPlatform\ArrayKeysValidator;
use Rollerworks\Component\Search\Elasticsearch\ElasticsearchFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchExtension.
 */
class SearchExtension implements QueryCollectionExtensionInterface
{
    private $requestStack;
    private $elasticsearchFactory;
    private $elasticaClient;

    public function __construct(RequestStack $requestStack, ElasticsearchFactory $elasticsearchFactory, Client $elasticaClient)
    {
        $this->requestStack = $requestStack;
        $this->elasticsearchFactory = $elasticsearchFactory;
        $this->elasticaClient = $elasticaClient;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var SearchCondition $condition */
        if (!$request || null === $condition = $request->attributes->get('_api_search_condition')) {
            return;
        }

        $context = $request->attributes->get('_api_search_context');
        $configuration = $request->attributes->get('_api_search_config');
        $configPath = "{$resourceClass}#attributes[rollerworks_search][contexts][{$context}][elasticsearch]";

        if (empty($configuration['elasticsearch'])) {
            return;
        }

        $configuration = (array) $configuration['elasticsearch'];
        ArrayKeysValidator::assertOnlyKeys($configuration, ['mappings'], $configPath);

        $ids =

        // this snippet looks weird, factory should create a
        $conditionGenerator = $this->elasticsearchFactory->createCachedConditionGenerator(
            $this->elasticsearchFactory->createConditionGenerator($condition)
        );

        foreach ($configuration['mappings'] as $fieldName => $mapping) {
            $conditionGenerator->registerField($fieldName, $mapping);
        }

        // TODO: temporary, how to do this better?
        $identifier = 'id';
        $search = new Search($this->elasticaClient);
        $response = $search->search($conditionGenerator->getQuery());
        $identifierValues = array_map(function (Document $document) {
            return $document->getId();
        }, $response->getDocuments());

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // straight from FOS Elastica Bundle
        $queryBuilder
            ->andWhere(
                $queryBuilder
                    ->expr()
                        ->in($rootAlias.'.'.$identifier, ':ids')
            )
            ->setParameter('ids', $identifierValues);
    }
}
