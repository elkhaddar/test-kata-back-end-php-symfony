<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/", name="account_")
 */
class AuthenticationController extends AbstractController
{
    private $em;
    private $jwtManager;
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->jwtManager = $jwtManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/api/account", name="create", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Vérification si l'email existe déjà
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['message' => 'Email already in use'], 400);
        }

        // Vérification si le nom d'utilisateur existe déjà
        $existingUsername = $this->em->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existingUsername) {
            return $this->json(['message' => 'Username already in use'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setFirstname($data['firstname']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $data['password']));

        // Validation
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation failed', 'errors' => (string) $errors], 400);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            'message' => 'User created',
            'user' => [
                'email' => $user->getEmail(),
                'username' => $user->getUsername()
            ]
        ], 201);
    }

    /**
     * @Route("/api/account/token", name="token", methods={"POST"})
     */
    public function token(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Recherche de l'utilisateur par email
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        // Vérification de l'existence de l'utilisateur et du mot de passe
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        if (!$this->passwordEncoder->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Invalid password'], 401);
        }

        // Générer et retourner le token
        $token = $this->jwtManager->create($user);
        return $this->json(['token' => $token]);
    }
}

            