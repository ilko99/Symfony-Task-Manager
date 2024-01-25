<?php

namespace App\Controller;

use App\Service\ProjectMemberService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectMemberController extends AbstractController
{
    private $projectMemberService;
    private $logger;

    public function __construct(ProjectMemberService $projectMemberService, LoggerInterface $logger)
    {
        $this->projectMemberService = $projectMemberService;
        $this->logger = $logger;
    }

    #[Route('/api/project/{projectId}/members', name: 'app_project_members', methods: ['GET'])]
    public function index(int $projectId): JsonResponse
    {
        try {
            $project = $this->projectMemberService->getProject($projectId);

            if (!$project)
            {
                return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
            }

            $currentUser = $this->getUser();

            if (!$project->getUsers()->contains($currentUser) && $currentUser !== $project->getCreator())
            {
                return $this->json(['error' => 'You are not a member of this project'], Response::HTTP_FORBIDDEN);
            }

            $projectMembers = $this->projectMemberService->getAllMembers($projectId);

            return $this->json([
                'data' => $projectMembers
            ]);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    #[Route('/api/project/{projectId}/members/{memberId}', name: 'app_project_single_member', methods: ['GET'])]
    public function show(int $projectId, int $memberId): JsonResponse
    {
        try{
            $member = $this->projectMemberService->getMember($projectId, $memberId, $this->getUser());

            if (!$member)
            {
                return $this->json(['error' => 'Member not found'], Response::HTTP_NOT_FOUND);
            }

            $memberData = [
                'id' => $member->getId(),
                'email' => $member->getEmail(),
                'roles' => $member->getRoles(),
            ];

            return $this->json([
                'data' => $memberData
            ], Response::HTTP_OK);
        }catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    #[Route('/api/project/{projectId}/members/{memberId}', name: 'app_project_store_member', methods: ['POST'])]
    public function store(int $projectId, int $memberId): JsonResponse
    {
        try {

            $this->projectMemberService->addMember($projectId, $memberId, $this->getUser());

            return $this->json([
                'message' => 'User successfully added to the project'
            ], Response::HTTP_OK);

        } catch (\Exception $e){
            return $this->handleException($e);
        }
    }

    #[Route('/api/project/{projectId}/members/{oldMemberId}/{newMemberId}', name: 'app_project_update_member', methods: ['PUT'])]
    public function update(int $projectId,int $oldMemberId,int $newMemberId): JsonResponse
    {
        try {

            $this->projectMemberService->updateMember($projectId, $oldMemberId, $newMemberId, $this->getUser());

            return $this->json([
                'message' => 'User successfully updated'
            ], Response::HTTP_OK);

        } catch (\Exception $e){
            return  $this->handleException($e);
        }
    }

    #[Route('/api/project/{projectId}/members/{memberId}', name: 'app_project_remove_member', methods: ['DELETE'])]
    public function destroy(int $projectId, int $memberId): JsonResponse
    {
        try {

            $this->projectMemberService->removeMember($projectId, $memberId, $this->getUser());

            return $this->json([
                'message' => 'User successfully removed from the project'
            ], Response::HTTP_OK);

        } catch (\Exception $e){
            return $this->handleException($e);
        }
    }

    private function handleException(\Exception $e) : JsonResponse
    {
        $this->logger->error($e->getMessage());
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $errorMessage = 'An unexpected error occured';
        if($e->getMessage() === 'Project not found' || $e->getMessage() === 'User not found'){
            $statusCode = Response::HTTP_NOT_FOUND;
            $errorMessage = $e->getMessage();
        }

        return $this->json([
            'error' => $errorMessage,
        ], $statusCode);
    }
}
