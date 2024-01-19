<?php

namespace App\Controller;

use App\Service\UserService;
use Cassandra\Exception\UnauthorizedException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/api/user/{id}', name: 'app_show_user')]
    public function show(int $id): Response
    {
        try {
            $userInfo = $this->userService->getUserInfo($id, $this->getUser());
        } catch (UserNotFoundException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (UnauthorizedException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'data' => $userInfo
        ]);
    }
}
