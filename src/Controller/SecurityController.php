<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
            'target_path' => $this->generateUrl('dashboard'),
            'username_label' => 'Имя пользователя',
            'password_label' => 'Пароль',
            'sign_in_label' => 'Войти',
            'username_parameter' => 'username',
            'password_parameter' => 'password',
            'remember_me_enabled' => true,
            'remember_me_parameter' => 'remember_me',
            'remember_me_checked' => false,
            'remember_me_label' => 'Запомнить меня',
            'csrf_token_intention' => 'authenticate',
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/sign-up', name: 'sign_up')]
    public function signUp(AuthenticationUtils $authenticationUtils): Response {
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('login/signup.html.twig', [
            'error' => $error,
            'target_path' => $this->generateUrl('dashboard'),
            'username_parameter' => 'email',
            'password_parameter' => 'password',
            'csrf_token_intention' => 'authenticate',
        ]);
    }

    #[Route(path: '/app-register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            $user = new User();
            $user->setUsername($email);
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('login/signup.html.twig');
    }
}
