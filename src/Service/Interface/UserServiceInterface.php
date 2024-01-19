<?php

namespace App\Service\Interface;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserServiceInterface
{
    public function getUserInfo(int $id, UserInterface $currentUser): array;
}