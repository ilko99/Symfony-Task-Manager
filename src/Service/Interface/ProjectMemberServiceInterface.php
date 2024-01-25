<?php

namespace App\Service\Interface;

use App\Entity\User;
interface ProjectMemberServiceInterface
{
    public function getAllMembers(int $projectId): array;
    public function getMember(int $projectId, int $memberId, ?User $currentUser): ?User;
    public function addMember(int $projectId, int $memberId, ?User $currentUser): void;
    public function updateMember(int $projectId, int $oldMemberId, int $newUserId, ?User $currentUser): void;
    public function removeMember(int $projectId, int $memberId, ?User $currentUser): void;

    public function getProject(int $projectId);
}