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
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Search;
use Rollerworks\Component\Search\ApiPlatform\ArrayKeysValidator;
use Rollerworks\Component\Search\Elasticsearch\ElasticsearchFactory;
use Rollerworks\Component\Search\Exception\BadMethodCallException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchExtension.
 */
class SearchExtension implements QueryCollectionExtensionInterface
{
    private $requestStack;
    private $registry;
    private $elasticsearchFactory;
    private $elasticaClient;
    private $identifierNames = [];

    public function __construct(RequestStack $requestStack, ManagerRegistry $registry, ElasticsearchFactory $elasticsearchFactory, Client $elasticaClient)
    {
        $this->requestStack = $requestStack;
        $this->registry = $registry;
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
        ArrayKeysValidator::assertOnlyKeys($configuration, ['mappings', 'identifiers_normalizer'], $configPath);

        // this snippet looks weird, factory should create the proper instance on its own
        $conditionGenerator = $this->elasticsearchFactory->createCachedConditionGenerator(
            $this->elasticsearchFactory->createConditionGenerator($condition)
        );

        foreach ($configuration['mappings'] as $fieldName => $mapping) {
            $conditionGenerator->registerField($fieldName, $mapping);
        }

        $normalizer = null;
        if (array_key_exists('identifiers_normalizer', $configuration)) {
            $normalizer = $configuration['identifiers_normalizer'];
            if (!is_callable($normalizer)) {
                throw new BadMethodCallException('Parameter "identifiers_normalizer" must be a valid callable');
            }
        }

        // TODO: temporary, how to do this better?
        $query = new Query($conditionGenerator->getQuery());

        // move limit/offset from QueryBuilder to Elasticsearch query
        if (null !== $firstResult = $queryBuilder->getFirstResult()) {
            $query->setFrom($firstResult);
            $queryBuilder->setFirstResult(null);
        }
        if (null !== $maxResults = $queryBuilder->getMaxResults()) {
            $query->setSize($maxResults);
            $queryBuilder->setMaxResults(null);
        }

        $search = new Search($this->elasticaClient);
        $mappings = $conditionGenerator->getMappings();
        foreach ($mappings as $mapping) {
            $index = $this->elasticaClient->getIndex($mapping->indexName);
            $type = $index->getType($mapping->typeName);
            $search
                ->addIndex($index)
                ->addType($type);
        }
        $response = $search->search($query);

        // NOTE: written like this so we only check if we have a normalizer once
        if (null !== $normalizer) {
            $callable = function (Document $document) use ($normalizer) {
                return call_user_func($normalizer, $document->getId());
            };
        } else {
            $callable = function (Document $document) {
                return $document->getId();
            };
        }
        $ids = array_map($callable, $response->getDocuments());

        // straight from FOS Elastica Bundle
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $identifier = $this->getIdentifierNames($resourceClass);

        // TODO: hack, only works for non-composite PKs
        $identifier = current($identifier);
        $queryBuilder
            ->andWhere(
                $queryBuilder
                    ->expr()
                        ->in($rootAlias.'.'.$identifier, ':ids')
            )
            ->setParameter('ids', $ids);
    }

    private function getIdentifierNames(string $class): array
    {
        if (!array_key_exists($class, $this->identifierNames)) {
            $manager = $this->registry->getManagerForClass($class);
            $metadata = $manager->getClassMetadata($class);

            $this->identifierNames[$class] = $metadata->getIdentifier();
        }

        return $this->identifierNames[$class];
    }
}
