<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ComputerRepository")
 */
class Computer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serialNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $macAddress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hostname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mediaType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diskSize;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $processor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ramSize;

    /**
     * @ORM\Column(type="datetime")
     */
    private $queryDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getMacAddress(): ?string
    {
        return $this->macAddress;
    }

    public function setMacAddress(?string $macAddress): self
    {
        $this->macAddress = $macAddress;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(?string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getDiskSize(): ?int
    {
        return $this->diskSize;
    }

    public function setDiskSize(?int $diskSize): self
    {
        $this->diskSize = $diskSize;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getProcessor(): ?string
    {
        return $this->processor;
    }

    public function setProcessor(?string $processor): self
    {
        $this->processor = $processor;

        return $this;
    }

    public function getRamSize(): ?int
    {
        return $this->ramSize;
    }

    public function setRamSize(int $ramSize): self
    {
        $this->ramSize = $ramSize;

        return $this;
    }

    public function getQueryDate(): ?\DateTimeInterface
    {
        return $this->queryDate;
    }

    public function setQueryDate(\DateTimeInterface $queryDate): self
    {
        $this->queryDate = $queryDate;

        return $this;
    }
}