<?php

namespace App\Service;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\Interface\ProjectServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService implements ProjectServiceInterface
{

    private EntityManagerInterface $entityManager;

    private ProjectRepository $projectRepository;

    public function __construct(EntityManagerInterface $entityManager, ProjectRepository $projectRepository)
    {
        $this->entityManager = $entityManager;
        $this->projectRepository = $projectRepository;
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

    public function get(int $id): Project
    {
        return $this->entityManager->getRepository(Project::class)->find($id);
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
}