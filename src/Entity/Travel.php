<?php

namespace App\Entity;

use App\Repository\TravelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass=TravelRepository::class)
 */
class Travel
{
    const STATUS_INITIATED = 'INITIATED';
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_VALIDATED = 'VALIDATED';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="travel")
     * @ORM\JoinColumn(nullable=false)
     */
    private $start;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="travel")
     * @ORM\JoinColumn(nullable=false)
     */
    private $finish;

    /**
     * @ORM\Column(type="integer")
     */
    private $placeNumber;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;



    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $images = [];

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="travel")
     */
    private $User;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=Reserve::class, mappedBy="travel")
     */
    private $reserves;

    public function __construct()
    {
        $this->reserves = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?City
    {
        return $this->start;
    }

    public function setStart(?City $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getFinish(): ?City
    {
        return $this->finish;
    }

    public function setFinish(?City $finish): self
    {
        $this->finish = $finish;

        return $this;
    }

    public function getPlaceNumber(): ?int
    {
        return $this->placeNumber;
    }

    public function setPlaceNumber(int $placeNumber): self
    {
        $this->placeNumber = $placeNumber;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }


    public function getImages(): array
    {
        $images = $this->images;

        return array_unique($images);
    }

    public function setImages(array $images)
    {
        $this->images = $images;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|Reserve[]
     */
    public function getReserves(): Collection
    {
        return $this->reserves;
    }

    public function addReserf(Reserve $reserf): self
    {
        if (!$this->reserves->contains($reserf)) {
            $this->reserves[] = $reserf;
            $reserf->setTravel($this);
        }

        return $this;
    }

    public function removeReserf(Reserve $reserf): self
    {
        if ($this->reserves->removeElement($reserf)) {
            // set the owning side to null (unless already changed)
            if ($reserf->getTravel() === $this) {
                $reserf->setTravel(null);
            }
        }

        return $this;
    }


    public function getNbrPlcReserve(): ?int
    {
        $countReserve = 0;
        $reserves = $this->getReserves();
        foreach ($reserves as $reserve){
            $countReserve = $countReserve +  $reserve->getPlaceNumber();
        }

        return ($this->getPlaceNumber() - $countReserve);
    }

}
