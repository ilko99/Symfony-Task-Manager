<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
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
}