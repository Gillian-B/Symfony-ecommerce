<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OrdersRepository::class)
 */
class Orders
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"order:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Groups({"order:read"})
     */
    private $totalPrice;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"order:read"})
     */
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"order:read"})
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Orderproducts::class, mappedBy="orders")
     * @Groups({"order:read"})
     */
    private $orderproducts;

    public function __construct()
    {
        $this->orderproducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Orderproducts[]
     */
    public function getOrderproducts(): Collection
    {
        return $this->orderproducts;
    }

    public function addOrderproduct(Orderproducts $orderproduct): self
    {
        if (!$this->orderproducts->contains($orderproduct)) {
            $this->orderproducts[] = $orderproduct;
            $orderproduct->setOrders($this);
        }

        return $this;
    }

    public function removeOrderproduct(Orderproducts $orderproduct): self
    {
        if ($this->orderproducts->removeElement($orderproduct)) {
            // set the owning side to null (unless already changed)
            if ($orderproduct->getOrders() === $this) {
                $orderproduct->setOrders(null);
            }
        }

        return $this;
    }
}
