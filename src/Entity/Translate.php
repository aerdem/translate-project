<?php

namespace App\Entity;

use App\Repository\TranslateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TranslateRepository::class)
 */
class Translate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $browser_unique_id;

    /**
     * @ORM\Column(type="json")
     */
    private $translate = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $test;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrowserUniqueid(): ?string
    {
        return $this->browser_unique_id;
    }

    public function setBrowserUniqueid(string $browser_unique_id): self
    {
        $this->browser_unique_id = $browser_unique_id;

        return $this;
    }

    public function getTranslate(): ?array
    {
        return $this->translate;
    }

    public function setTranslate(array $translate): self
    {
        $this->translate = $translate;

        return $this;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(string $test): self
    {
        $this->test = $test;

        return $this;
    }
}
