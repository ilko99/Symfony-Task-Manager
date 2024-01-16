<?php

namespace App\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\Interface\TaskServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class TaskService implements TaskServiceInterface
{
    private $entityManager;

    private $taskRepository;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
    }

    public function getAllTasks(): array
    {
        try{
            $tasks = $this->taskRepository->findAll();

            return array_map(function (Task $task){
                return [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'is_done' => $task->isIsDone(),
                    'created_at' => $task->getCreatedAt() ? $task->getCreatedAt()->format('Y-m-d H:i:s') : null,
                ];
            }, $tasks);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    public function createTasks(array $apiTask): Task
    {
        $task = new Task();

        $task->setTitle($apiTask['title'] ?? null);
        $task->setIsDone($apiTask['is_done'] ?? false);

        $now = new \DateTimeImmutable();
        $task->setCreatedAt($now);
        $task->setUpdatedAt($now);

        try {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $task;
    }

    public function getTask(int $id): ?Task
    {
        return $this->taskRepository->find($id);
    }

    public function updateTask(int $id, array $taskData): ?Task
    {
        $task = $this->taskRepository->find($id);

        if (!$task)
        {
         throw new \Exception('Task not found');
        }

        if (isset($taskData['title']))
        {
            $task->setTitle($taskData['title']);
        }

        if (isset($taskData['is_done']))
        {
            $task->setIsDone($taskData['is_done']);
        }

        $task->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $task;
    }

    public function deleteTask(int $id): void
    {
        $task = $this->taskRepository->find($id);

        if(!$task)
        {
            throw new \Exception('Task not found');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

    }
}