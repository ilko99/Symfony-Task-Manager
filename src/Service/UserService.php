<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Interface\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserService implements UserServiceInterface
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordEncoder;

    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    public function createUser(array $userData): User
    {
        $user = new User();

        $user->setEmail($userData['email'] ?? null);

        $hashedPassword = $this->passwordEncoder->hashPassword($user, $userData['password'] ?? null);

        $user->setPassword($hashedPassword);

        $user->setRoles('ROLE_USER');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getUserInfo(int $id, UserInterface $currentUser): array
    {
        if($id !== $currentUser->getId()){
            throw new UserNotFoundException('You can only view your own user info');
        }

        $user = $this->userRepository->find($id);

        if (!$user){
            throw new UserNotFoundException('User not found');
        }

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }
}