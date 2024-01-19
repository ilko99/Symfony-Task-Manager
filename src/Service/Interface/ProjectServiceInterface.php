<?php

namespace App\Service\Interface;

use App\Entity\Project;

interface ProjectServiceInterface
{
    public function getAll(): array;
    public function create(array $apiProjectData): Project;
    public function get(int $id): array;
    public function update(int $id, array $projectData): Project;
    public function delete(int $id): void;
}