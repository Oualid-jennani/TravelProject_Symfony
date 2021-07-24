<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\CustomerRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


/**
 * Class SuperAdminController.
 */

class SecurityController extends AbstractController
{
    public function __construct()
    {

    }

    /**
     * @Route("/login", name="security_login")
     * @param Request             $request
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        if($error) {
            $this->addFlash('error', $error->getMessage());

            $ref = $request->headers->get('referer');
            if (!is_string($ref) || $ref) {
                return $this->redirect($ref);
            }
        }

        if ($request->request->has('_password') || $request->request->has('_username')) {
            $ref = $request->headers->get('referer');
            if (!is_string($ref) || $ref) {
                return $this->redirect($ref);
            }
        }

        return $this->render('frontOffice/security/login.html.twig');
    }

    /**
     * @Route("/logout" , name="security_logout")
     */
    public function logout(){}



    //<editor-fold desc="Registration Customer">
    /**
     * @Route("/registration/customer", name="registrationCustomer")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function registrationCustomer(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ): Response{
        $user = new User();
        $form = $this->createForm(CustomerRegistrationType::class,$user);
        $form ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $hash = $encoder->encodePassword($user,$user->getPassword());
            $user->setPassword($hash);
            $user->setRoles(['ROLE_CUSTOMER']);
            $user->setStatus('notConfirmed');
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success','Thank you for your registration !');
            return $this->render('frontOffice/security/login.html.twig');
        }

        return $this->render('frontOffice/security/signUpCustomer.html.twig',[
            'form'=>$form->createView(),
        ]);
    }
    //</editor-fold>


    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(): Response
    {
        if ($this->getUser()) {
            $role = $this->getUser()->getRoles();
            $routes = [
                'ROLE_ADMIN' => 'dashboardAdmin',
                'ROLE_CUSTOMER'=>'index',
            ];

            return $this->redirectToRoute($routes[$role[0]]);
        }

        return $this->redirectToRoute('index');
    }


}
