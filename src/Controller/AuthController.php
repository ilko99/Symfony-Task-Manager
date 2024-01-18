<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/api/register', name: 'app_auth')]
    public function register(Request $request): JsonResponse
    {
      $data = json_decode($request->getContent(), true);

      if(!isset($data['email']) || !isset($data['password'])) {
          return new JsonResponse('Missing email or password', Response::HTTP_BAD_REQUEST);
      }

      try{
          $user = $this->userService->createUser($data);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }

      return $this->json([
          'data' => [
              'id' => $user->getId(),
              'email' => $user->getEmail(),
              'roles' => $user->getRoles()
          ]
      ]);
    }
}
