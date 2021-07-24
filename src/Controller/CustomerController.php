<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\EditCustomerType;
use App\Form\Model\ChangePassword;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * Class CustomerController
 * @package App\Controller
 * @IsGranted("ROLE_CUSTOMER")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/account-info", name="account-info")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function profile(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditCustomerType::class,$user);
        $form->handleRequest($request);
        $changePasswordModel = new ChangePassword();
        $formPassword = $this->createForm(ChangePasswordType::class,$changePasswordModel);
        $formPassword->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

           try {
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Information Edited');

            } catch (\Exception $exception) {
                $this->addFlash('error', 'Error');
            }

        } elseif ($formPassword->isSubmitted() && $formPassword->isValid()) {

            try {
                $hash = $encoder->encodePassword($user,$changePasswordModel->getNewPassword());
                $user->setPassword($hash);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('successPass', 'Password Changed');

            } catch (\Exception $exception) {
                $this->addFlash('errorPass', 'Error');
            }

        }

        return $this->render('frontOffice/customer/account-info.html.twig',[
            'form'=>$form->createView(),
            'formPassword'=>$formPassword->createView(),
        ]);
    }

    /**
     * @Route("/travel-history", name="travelHistory")
     */
    public function travelHistory() {
        /**
         * @var User $customer
         */
        $customer = $this->getUser();
        $travels = $customer->getTravel();

        return $this->render('frontOffice/customer/travel_history.html.twig',[
            'travels'=> $travels,
        ]);

    }

}
