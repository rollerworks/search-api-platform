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

namespace Rollerworks\Component\Search\ApiPlatform\Tests\Fixtures;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dummy.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 *
 * @ORM\Entity
 */
#[ApiResource(filters: ['my_dummy.search', 'my_dummy.order', 'my_dummy.date', 'my_dummy.range', 'my_dummy.boolean', 'my_dummy.numeric'])]
class Dummy
{
    /**
     * @var int The id
     *
     * @ORM\Column(type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string The dummy name
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     *
     * @ApiProperty(iri="http://schema.org/name")
     */
    private $name;

    /**
     * @var string The dummy name alias
     *
     * @ORM\Column(nullable=true)
     *
     * @ApiProperty(iri="https://schema.org/alternateName")
     */
    private $alias;

    /**
     * @var array foo
     */
    protected $foo;

    /**
     * @var string A short description of the item
     *
     * @ORM\Column(nullable=true)
     *
     * @ApiProperty(iri="https://schema.org/description")
     */
    public $description;

    /**
     * @var string A dummy
     *
     * @ORM\Column(nullable=true)
     */
    public $dummy;

    /**
     * @var bool A dummy boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    public $dummyBoolean;

    /**
     * @var \DateTimeImmutable A dummy date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    public $dummyDate;

    /**
     * @var string A dummy float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    public $dummyFloat;

    /**
     * @var string A dummy price
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    public $dummyPrice;

    /**
     * @var RelatedDummy A related dummy
     *
     * @ORM\ManyToOne(targetEntity="RelatedDummy")
     */
    public $relatedDummy;

    /**
     * @var ArrayCollection Several dummies
     *
     * @ORM\ManyToMany(targetEntity="RelatedDummy")
     */
    public $relatedDummies;

    /**
     * @var array serialize data
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    public $jsonData;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    public $nameConverted;

    public static function staticMethod(): void
    {
    }

    public function __construct()
    {
        $this->relatedDummies = new ArrayCollection();
        $this->jsonData = [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setAlias($alias): void
    {
        $this->alias = $alias;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function hasRole($role): void
    {
    }

    public function setFoo(?array $foo = null): void
    {
    }

    public function setDummyDate(?\DateTimeImmutable $dummyDate = null): void
    {
        $this->dummyDate = $dummyDate;
    }

    public function getDummyDate()
    {
        return $this->dummyDate;
    }

    public function setDummyPrice($dummyPrice)
    {
        $this->dummyPrice = $dummyPrice;

        return $this;
    }

    public function getDummyPrice()
    {
        return $this->dummyPrice;
    }

    public function setJsonData($jsonData): void
    {
        $this->jsonData = $jsonData;
    }

    public function getJsonData()
    {
        return $this->jsonData;
    }

    public function getRelatedDummy()
    {
        return $this->relatedDummy;
    }

    /**
     * @return bool
     */
    public function isDummyBoolean()
    {
        return $this->dummyBoolean;
    }

    /**
     * @param bool $dummyBoolean
     */
    public function setDummyBoolean($dummyBoolean): void
    {
        $this->dummyBoolean = $dummyBoolean;
    }

    public function setDummy($dummy = null): void
    {
        $this->dummy = $dummy;
    }

    public function getDummy()
    {
        return $this->dummy;
    }
}
