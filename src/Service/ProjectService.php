<?php

namespace App\Service;
use App\Entity\Project;
use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\Interface\ProjectServiceInterface;
use Doctrine\Common\Collections\Collection;
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
            return array_map(function (Project $project){
                return [
                    'id' => $project->getId(),
                    'title' => $project->getTitle(),
                    'created_at' => $project->getCreatedAt() ? $project->getCreatedAt()->format('Y-m-d H:i:s') : null,
                ];
            }, $projects);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return [];
        }

    }

    public function create(array $apiProjectData): Project
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

        return $project;
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
    public function update(int $id, array $projectData): Project
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

        return $project;
    }

    public function delete(int $id): void
    {
        $project = $this->projectRepository->find($id);

        if(!$project)
        {
            throw new \Exception('Project not found');
        }

        $this->entityManager->remove($project);
        $this->entityManager->flush();
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

    private function translateTaskToArray(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'is_done' => $task->isIsDone(),
            'created_at' => $task->getCreatedAt()->format('c'),
            'updated_at' => $task->getUpdatedAt()->format('c'),
            'project_id' => $task->getProject() ? $task->getProject()->getId() : null,
            'creator_id' => $task->getCreator() ? $task->getCreator()->getId() : null,
        ];
    }
}