<?php

namespace App\Controller;

use App\Service\Interface\ProjectServiceInterface;
use Psr\Log\LoggerInterface;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    private $projectService;

    private $logger;

    public function __construct(ProjectServiceInterface $projectService, LoggerInterface $logger)
    {
        $this->projectService = $projectService;
        $this->logger = $logger;
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
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'data' => $project
        ]);
    }

    #[Route('/api/projects/{id}', name: 'app_get_project', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try{
            $project = $this->projectService->get($id);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'data' => $project
        ], Response::HTTP_OK);
    }

    #[Route('/api/projects/{id}', name: 'app_update_project', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $apiData = json_decode($request->getContent(), true);

        if (!isset($apiData['title'])) {
            return new JsonResponse('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        try{
            $projectArray = $this->projectService->update($id, $apiData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

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
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
