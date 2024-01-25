<?php

namespace App\Service;
use App\Entity\Project;
use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\Interface\ProjectServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService implements ProjectServiceInterface
{

    private EntityManagerInterface $entityManager;

    private ProjectRepository $projectRepository;

    private TaskRepository $taskRepository;

    public function __construct(EntityManagerInterface $entityManager, ProjectRepository $projectRepository, TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
    }

    public function getAll(): array
    {
        try{
            $projects = $this->projectRepository->findAll();
            return array_map([$this, 'translateProjectToArray'],$projects);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return [];
        }

    }

    public function create(array $apiProjectData): array
    {
        $project = new Project();
        $project->setTitle($apiProjectData['title'] ?? null);

        $now = new \DateTimeImmutable();
        $project->setCreatedAt($now);
        $project->setUpdatedAt($now);

        try {
            $this->entityManager->persist($project);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $this->translateProjectToArray($project);
    }

    public function get(int $id): array
    {
        $project = $this->entityManager->getRepository(Project::class)->find($id);

        if(!$project)
        {
            throw new \Exception('Project not found');
        }

        $tasks = $this->taskRepository->findByProjectId($project->getId());

        return [
            'project' => $this->translateProjectToArray($project),
            'tasks' => array_map([$this, 'translateTaskToArray'], $tasks)
        ];
    }
    public function update(int $id, array $projectData): array
    {
        $project = $this->projectRepository->find($id);

        if(!$project)
        {
            throw new \Exception('Project not found');
        }

        if(isset($projectData['title']))
        {
            $project = $project->setTitle($projectData['title']);
        }

        $project->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->translateProjectToArray($project);
    }

    public function delete(int $id): void
    {
        $tasks = $this->taskRepository->findByProjectId($id);

        foreach ($tasks as $task){
            $this->entityManager->remove($task);
        }

        $project = $this->projectRepository->find($id);

        if(!$project)
        {
            throw new \Exception('Project not found');
        }

        $this->entityManager->remove($project);
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
            'creator_id' => $task->getCreator()->getId(),
        ];
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