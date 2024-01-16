<?php

namespace App\Service\Interface;

use App\Entity\Task;

interface TaskServiceInterface
{
    public function getAllTasks(): array;
    public function createTasks(array $apiTask): Task;
    public function getTask(int $id): ?Task;
    public function updateTask(int $id, array $taskData): ?Task;
    public function deleteTask(int $id): void;

}