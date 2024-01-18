<?php

namespace App\Controller;

use App\Entity\Project;
use App\Service\Interface\ProjectServiceInterface;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    private ProjectServiceInterface $projectService;

    public function __construct(ProjectServiceInterface $projectService)
    {
        $this->projectService = $projectService;
    }
    #[Route('/api/projects', name: 'app_project', methods: ['GET'])]
    public function index(): JsonResponse
    {
      $projects = $this->projectService->getAll();

      return $this->json([
          'data' => $projects
      ]);
    }

    #[Route('/api/projects', name: 'app_store_project', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $apiData = json_decode($request->getContent(), true);

        if (!isset($apiData['title'])) {
            return new JsonResponse('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        try{
            $project = $this->projectService->create($apiData);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $projectArray = $this->translateProjectToArray($project);

        return $this->json([
            'data' => $projectArray
        ]);
    }

    #[Route('/api/projects/{id}', name: 'app_get_project', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try{
            $project = $this->projectService->get($id);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $projectArray = $this->translateProjectToArray($project);

        return $this->json([
            'data' => $projectArray
        ]);
    }

    #[Route('/api/projects/{id}', name: 'app_update_project', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $apiData = json_decode($request->getContent(), true);

        if (!isset($apiData['title'])) {
            return new JsonResponse('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        try{
            $project = $this->projectService->update($id, $apiData);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $projectArray = $this->translateProjectToArray($project);

        return $this->json([
            'data' => $projectArray
        ]);
    }

    #[Route('/api/projects/{id}', name: 'app_delete_project', methods: ['DELETE'])]
    public function destroy(int $id): JsonResponse
    {
        try{
            $this->projectService->delete($id);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function translateProjectToArray(Project $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'created_at' => $task->getCreatedAt()->format('c'),
            'updated_at' => $task->getUpdatedAt()->format('c'),
        ];
    }
}
