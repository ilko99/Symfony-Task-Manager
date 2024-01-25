<?php

namespace App\Service;

use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Service\Interface\ProjectMemberServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Psr\Log\LoggerInterface;

class ProjectMemberService implements ProjectMemberServiceInterface
{

    private  $entityManager;
    private  $projectRepository;
    private  $userRepository;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, ProjectRepository $projectRepository, UserRepository $userRepository, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function getAllMembers(int $projectId): array
    {
        try {
            $project = $this->projectRepository->find($projectId);

            if (!$project)
            {
                throw new \Exception('Project not found');
            }

            $members = $project->getUsers();
            $memberData = [];

            foreach($members as $member){
                $memberData[] = [
                    'id' => $member->getId(),
                    'email' => $member->getEmail(),
                    'roles' => $member->getRoles(),
                ];
            }

            return $memberData;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    public function getProject(int $projectId)
    {
        $project = $this->projectRepository->find($projectId);

        if(!$project)
        {
            throw new \Exception('Project not found');
        }

        return $project;
    }

    public function getMember(int $projectId, int $memberId, ?User $currentUser): ?User
    {
        try{
            $project = $this->projectRepository->find($projectId);

            if (!$project)
            {
                throw new \Exception('Project not found');
            }

            if ($project->getCreator() && $currentUser !== $project->getCreator())
            {
                throw new \Exception('Only the creator of the project can view members');
            }

            $member = $project->getUsers()->filter(function(User $user) use ($memberId){
                return $user->getId() === $memberId;
            })->first();

            return $member ?: null;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    public function addMember(int $projectId, int $memberId, ?User $currentUser): void
    {

        $project = $this->projectRepository->find($projectId);
        $member = $this->userRepository->find($memberId);

        if (!$project)
        {
            throw new \Exception('Project not found');
        }

        if (!$member)
        {
            throw new \Exception('User not found');
        }

        if ($project->getCreator() && $currentUser !== $project->getCreator())
        {
            throw new \Exception('Only the creator of the project can add members');
        }

        try {
            $project->addUser($member);

            $this->entityManager->flush();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    public function updateMember(int $projectId, int $oldMemberId, int $newUserId, ?User $currentUser): void
    {

        $project = $this->projectRepository->find($projectId);
        $oldMember = $this->userRepository->find($oldMemberId);
        $newUser = $this->userRepository->find($newUserId);

        if (!$project)
        {
            throw new \Exception('Project not found');
        }

        if (!$oldMember || !$newUser) {
            throw new \Exception('User not found');
        }

        if ($project->getCreator() && $currentUser !== $project->getCreator())
        {
            throw new \Exception('Only the creator of the project can update members');
        }

        try {
            $this->entityManager->beginTransaction();

            $project->removeUser($oldMember);
            $project->addUser($newUser);

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Exception $e) {
            $this->entityManager->rollback();

            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    public function removeMember(int $projectId, int $memberId, ?User $currentUser): void
    {
        $project = $this->projectRepository->find($projectId);
        $member = $this->userRepository->find($memberId);

        if (!$project)
        {
            throw new \Exception('Project not found');
        }

        if (!$member)
        {
            throw new \Exception('User not found');
        }

        if ($project->getCreator() && $currentUser !== $project->getCreator())
        {
            throw new \Exception('Only the creator of this project can remove members');
        }

        try {
            $project->removeUser($member);

            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

    }
}