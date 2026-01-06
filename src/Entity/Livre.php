<?php

namespace App\Entity;

use App\Repository\LivreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivreRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Livre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'année de publication est obligatoire")]
    #[Assert\Range(
        min: 1000,
        max: 2100,
        notInRangeMessage: "L'année doit être entre {{ min }} et {{ max }}"
    )]
    private ?int $annePublication = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 5000,
        maxMessage: "Le résumé ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $resume = null;

    #[ORM\Column]
    private ?bool $disponible = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateAjout = null;

    #[ORM\ManyToOne(inversedBy: 'livres')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'auteur est obligatoire")]
    private ?Auteur $auteur = null;

    public function __construct()
    {
        $this->disponible = true;
        $this->dateAjout = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getAnnePublication(): ?int
    {
        return $this->annePublication;
    }

    public function setAnnePublication(int $annePublication): static
    {
        $this->annePublication = $annePublication;
        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): static
    {
        $this->resume = $resume;
        return $this;
    }

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): static
    {
        $this->disponible = $disponible;
        return $this;
    }

    public function getDateAjout(): ?\DateTimeImmutable
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeImmutable $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

    public function getAuteur(): ?Auteur
    {
        return $this->auteur;
    }

    public function setAuteur(?Auteur $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    /**
     * Méthode automatique avant la persistance
     */
    #[ORM\PrePersist]
    public function setDateAjoutValue(): void
    {
        if ($this->dateAjout === null) {
            $this->dateAjout = new \DateTimeImmutable();
        }
    }

    /**
     * Affichage du livre sous forme de chaîne
     */
    public function __toString(): string
    {
        return $this->titre ?? 'Nouveau livre';
    }

    /**
     * Vérifie si le livre est récent (moins de 2 ans)
     */
    public function isRecent(): bool
    {
        $currentYear = (int) date('Y');
        return ($currentYear - $this->annePublication) <= 2;
    }

    /**
     * Retourne la date d'ajout formatée
     */
    public function getDateAjoutFormatted(string $format = 'd/m/Y'): string
    {
        return $this->dateAjout?->format($format) ?? '';
    }

    /**
     * Marque le livre comme emprunté
     */
    public function emprunter(): void
    {
        $this->disponible = false;
    }

    /**
     * Marque le livre comme retourné
     */
    public function retourner(): void
    {
        $this->disponible = true;
    }
}