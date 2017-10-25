<?php

declare(strict_types=1);

namespace Rollerworks\Component\Search\ApiPlatform\Elasticsearch\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Rollerworks\Component\Search\Elasticsearch\ElasticsearchFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchExtension.
 */
class SearchExtension implements QueryCollectionExtensionInterface
{
    private $requestStack;
    private $ormFactory;

    public function __construct(RequestStack $requestStack, ElasticsearchFactory $ormFactory)
    {
        $this->requestStack = $requestStack;
        $this->ormFactory = $ormFactory;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        throw new \Exception('Not implemented yet');
    }
}
