<?php

namespace LaFourchette\FixturesBundle\Tests\Behat\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChildDummy.
 *
 * @ORM\Entity
 */
class ChildDummy
{
    /**
     * @var int The id.
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $name;

    /**
     * @var Dummy
     * @ORM\ManyToOne(targetEntity="Dummy")
     */
    private $parent;

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
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Dummy $parent
     */
    public function setParent(Dummy $parent)
    {
        $this->parent = $parent;
    }
}
