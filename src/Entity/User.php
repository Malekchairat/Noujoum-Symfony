<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_user', type: 'integer')]
    private ?int $id_user = null;
   
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $mdp = null;

    #[ORM\Column(type: 'integer')]
    private $tel;    

    #[ORM\Column(type: 'string', length: 30)]
    private string $role;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /*
    // FAVORIS ASSOCIATION REMOVED TEMPORARILY
    #[ORM\OneToMany(targetEntity: Favoris::class, mappedBy: 'user')]
    private Collection $favoris;
    */

    public function __construct()
    {
        // Removed Favoris initialization since Favoris module is not ready.
        // $this->favoris = new ArrayCollection();
    }

    public function getIdUser(): ?string
    {
        return $this->id_user;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): static
    {
        $this->mdp = $mdp;
        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;
        return $this;
    }

    // Use getter and setter for RoleEnum conversion
    public function getRole(): RoleEnum
    {
        return RoleEnum::from($this->role); // Convert the string back to the enum
    }

    public function setRole(RoleEnum $role): self
    {
        $this->role = $role->value; // Store the enum value as a string
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    // Implementing UserInterface methods

    public function getUserIdentifier(): string
    {
        return $this->email; // Typically, you use email as the user identifier
    }

    public function getPassword(): string
    {
        return $this->mdp; // Return the password field
    }

    public function getRoles(): array
    {
        return [$this->getRole()->value]; // Return an array of roles
    }

    public function getSalt(): ?string
    {
        return null; // No salt needed if you're using bcrypt or sodium
    }

    public function eraseCredentials()
    {
        // If you store any temporary sensitive data, clear it here
    }

    public function uploadProfilePicture(Request $request)
    {
        $user = $this->getUser(); // Assuming you're fetching the current logged-in user
        $form = $this->createForm(ProfilePictureType::class, $user); // Your form type to handle the image upload
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $user->getImage(); // Assuming 'getImage' returns the file (UploadedFile)

            if ($file) {
                // Generate a unique name for the file before saving it
                $filename = uniqid() . '.' . $file->guessExtension();

                try {
                    // Move the file to the 'uploads' directory
                    $file->move(
                        $this->getParameter('uploads_directory'), // Configure 'uploads_directory' in services.yaml
                        $filename
                    );

                    // Set the file name in the User entity
                    $user->setImage($filename);

                    // Save the user with the new image
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();

                    $this->addFlash('success', 'Profile picture updated successfully!');
                } catch (FileException $e) {
                    // Handle exception if something goes wrong with the file upload
                    $this->addFlash('error', 'Could not upload the image.');
                }
            }
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /*
    // Favoris methods removed temporarily since the Favoris module is not ready.

    /**
     * @return Collection<int, Favoris>
     *\/
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favoris $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setUser($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getUser() === $this) {
                $favori->setUser(null);
            }
        }

        return $this;
    }
    */
}
