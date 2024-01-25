<?php

namespace App\Service\Interface;

use App\Entity\Task;

interface TaskServiceInterface
{
    public function getAllTasks(): array;
    public function createTask(array $apiTask): array;
    public function getTask(int $id): ?array;
    public function updateTask(int $id, array $taskData): ?array;
    public function deleteTask(int $id): void;

}