<?php

namespace LaFourchette\FixturesBundle\Tests\Behat\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RelatedDummy.
 *
 * @ORM\Entity
 */
class RelatedDummy
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(nullable=false)
     */
    private $name;

    /**
     * @var ChildDummy
     *
     * @ORM\ManyToOne(targetEntity="ChildDummy")
     */
    private $relatedDummy;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return ChildDummy
     */
    public function getRelatedDummy()
    {
        return $this->relatedDummy;
    }

    /**
     * @param ChildDummy $relatedDummy
     */
    public function setRelatedDummy(ChildDummy $relatedDummy)
    {
        $this->relatedDummy = $relatedDummy;
    }
}
