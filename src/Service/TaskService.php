<?php

namespace App\Service;

use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\Interface\TaskServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class TaskService implements TaskServiceInterface
{
    private $entityManager;

    private $taskRepository;

    private $projectRepository;

    private $security;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository, ProjectRepository $projectRepository, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->projectRepository = $projectRepository;
        $this->security = $security;
    }

    public function getAllTasks(): array
    {
        try{
            $tasks = $this->taskRepository->findAll();

            return array_map([$this, 'translateTaskToArray'],$tasks);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    public function createTask(array $apiTask): array
    {
        $task = new Task();

        $task->setTitle($apiTask['title'] ?? null);
        $task->setIsDone($apiTask['is_done'] ?? false);

        $now = new \DateTimeImmutable();
        $task->setCreatedAt($now);
        $task->setUpdatedAt($now);

        if (isset($apiTask['project_id'])) {
            $project = $this->projectRepository->find($apiTask['project_id']);
            if (!$project) {
                throw new \Exception('Project not found');
            }
            $task->setProject($project);
        }

        $user = $this->security->getUser();

        if(!$user){
            throw new \Exception('User not found or not authenticated');
        }

        if (!$user instanceof \App\Entity\User) {
            throw new \Exception('Authenticated user is not a valid User entity');
        }

        $userId = $user->getId();

        if(!$userId){
            throw new \Exception('User not found or not authenticated');
        }

        $task->setCreator($user);

        try {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $this->translateTaskToArray($task);
    }

    public function getTask(int $id): ?array
    {
        $task = $this->taskRepository->find($id);

        if(!$task)
        {
            throw new \Exception('Task not found');
        }

        return $this->translateTaskToArray($task);
    }

    public function updateTask(int $id, array $taskData): ?array
    {
        $task = $this->taskRepository->find($id);

        if (!$task)
        {
         throw new \Exception('Task not found');
        }

        $user = $this->security->getUser();

        if(!$user){
            throw new \Exception('User not found or not authenticated');
        }

        if ($task->getCreator() !== $user) {
            throw new \Exception('User is not the creator of the task');
        }

        if (isset($taskData['title']))
        {
            $task->setTitle($taskData['title']);
        }

        if (isset($taskData['is_done']))
        {
            $task->setIsDone($taskData['is_done']);
        }

        if (isset($taskData['project_id']))
        {
            $project = $this->projectRepository->find($taskData['project_id']);
            if ($project) {
                $task->setProject($project);
            }
        }

        $task->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->translateTaskToArray($task);
    }

    public function deleteTask(int $id): void
    {
        $task = $this->taskRepository->find($id);

        if(!$task)
        {
            throw new \Exception('Task not found');
        }

        $user = $this->security->getUser();

        if(!$user){
            throw new \Exception('User not found or not authenticated');
        }

        if ($task->getCreator() !== $user) {
            throw new \Exception('User is not the creator of the task');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }

    private function translateTaskToArray(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'is_done' => $task->getIsDone(),
            'created_at' => $task->getCreatedAt()->format('c'),
            'updated_at' => $task->getUpdatedAt()->format('c'),
            'project_id' => $task->getProject() ? $task->getProject()->getId() : null,
            'creator_id' => $task->getCreator() ? $task->getCreator()->getId() : null,
        ];
    }
}