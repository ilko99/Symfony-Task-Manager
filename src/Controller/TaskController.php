<?php

namespace App\Controller;

use App\Entity\Task;
use App\Service\TaskService;
use Psr\Log\LoggerInterface;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

class TaskController extends AbstractController
{

    private $taskService;
    private $logger;

    public function __construct(TaskService $taskService, LoggerInterface $logger)
    {
        $this->taskService = $taskService;
        $this->logger = $logger;
    }

    #[Route('/api/tasks', name: 'app_tasks', methods: ['GET'])]
    public function index(): JsonResponse
    {
      $tasksArray = $this->taskService->getAllTasks();

      return $this->json([
          'data' => $tasksArray
      ]);
    }


    #[Route('/api/tasks', name: 'app_store_tasks', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'])) {
            return new JsonResponse('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        try {
            $task = $this->taskService->createTask($data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'data' => $task
    ]);
    }

    #[Route('/api/tasks/{id}', name: 'app_get_task', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTask($id);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $this->json([
            'data' => $task
        ]);
    }

    #[Route('/api/tasks/{id}', name: 'app_update_task', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'])) {
            return new JsonResponse('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        try {
            $task = $this->taskService->updateTask($id, $data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'data' => $task
        ]);
    }

    #[Route('/api/tasks/{id}', name: 'app_delete_task', methods: ['DELETE'])]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->taskService->deleteTask($id);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
