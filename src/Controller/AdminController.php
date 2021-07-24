<?php

declare(strict_types=1);

namespace App\Controller;
use App\Entity\City;
use App\Entity\Country;

use App\Entity\Travel;
use App\Entity\User;
use App\Form\AdminType;
use App\Form\CountryType;
use App\Form\CityType;

use App\Form\Model\AssertTravelsImage;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use App\Service\ImageManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin")
 */

class AdminController extends AbstractController
{
    private $security;
    private $session;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var ImageManager
     */
    private $imageManager;


    /**
     * StoreController constructor.
     * @param Security $security
     * @param SessionInterface $session
     * @param EntityManagerInterface $manager
     * @param ImageManager $imageManager
     */
    public function __construct(
        Security $security,
        SessionInterface $session,
        EntityManagerInterface $manager,
        ImageManager $imageManager
    ) {
        $this->security = $security;
        $this->session = $session;
        $this->manager = $manager;
        $this->imageManager = $imageManager;
    }


    //<editor-fold desc="Code dashboardAdmin">
    /**
     * @Route("/", name="dashboardAdmin")
     */
    public function index(): Response
    {
        return $this->render('backOffice/admin/index.html.twig');
    }
    //</editor-fold>

    //<editor-fold desc="Code register and login">
    /**
     * @Route("/login", name="adminLogin")
     */
    public function login(): Response
    {
        return $this->render('BackOffice/admin/login.html.twig');
    }

    /**
     * @Route("/register", name="adminRegister")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, EntityManagerInterface $manager,UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(AdminType::class,$user);
        $form ->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()){
            try {
                $hash = $encoder->encodePassword($user,$user->getPassword());
                $user->setPassword($hash);
                $user->setRoles(['ROLE_ADMIN']);
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('adminLogin');

            }catch (Exception $ex){
                $this->addFlash('error','error');
            }
        }

        return $this->render('backOffice/admin/signUp.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    //</editor-fold>



    //<editor-fold desc="Code Profile">
    /**
     * @Route("/profile",name="dashAdminProfile")
     * @param Request $request
     * @return Response
     */
    public function profile(Request $request):Response
    {
        /**
         * @var User $admin
         */
        $admin = $this->getUser();


        return $this->render('backOffice/admin/account/profile.html.twig',[
            'admin'=>$admin
        ]);
    }
    //</editor-fold>


    //<editor-fold desc="Code country">
    /**
     * @Route("/countries", name="listCountries")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function listCounties(Request $request, EntityManagerInterface $manager): Response
    {
        $country = new Country();
        $form = $this->createForm(CountryType::class,$country);
        $form ->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()){
                $manager->persist($country);
                $manager->flush();

                return $this->redirectToRoute("listCountries");
            }
        }catch (Exception $ex){
            $this->addFlash('error','error');
        }

        $Countries = $this->getDoctrine()->getRepository(Country::class)->findAll();

        return $this->render('BackOffice/admin/country/listCountries.html.twig', [
            'Countries' => $Countries,
            'form'=>$form->createView(),
        ]);
    }


    /**
     * @Route("/countries/edit/{id}" , name="editCountry")
     * @param Country $country
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public  function editCountry(Country $country,Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(CountryType::class,new Country());
        $form ->handleRequest($request);
        $formEdit = $this->createForm(CountryType::class,$country);
        $formEdit ->handleRequest($request);

        try {
            if ($formEdit->isSubmitted() && $formEdit->isValid()){
                $entityManager->flush();
                return $this->redirectToRoute("listCountries");
            }
        }catch (Exception $ex){
            $this->addFlash('error','error');
        }
        $Countries = $this->getDoctrine()->getRepository(Country::class)->findAll();

        return $this->render('BackOffice/admin/country/listCountries.html.twig', [
            'Countries' => $Countries,
            'form'=>$form->createView(),
            'formEdit'=>$formEdit->createView(),
        ]);
    }

    /**
     * @Route("/countries/delete/{id}" , name="deleteCountry")
     * @param Country $country
     * @return RedirectResponse
     */
    public  function deleteCountry(Country $country)
    {
        $entityManager = $this->getDoctrine()->getManager();
       // $country = $entityManager->getRepository(Country::class)->find($id);
        $entityManager->remove($country);
        $entityManager->flush();

        return $this->redirectToRoute("listCountries");
    }
    //</editor-fold>

    //<editor-fold desc="Code City">
    /**
     * @Route("/cities", name="listCities")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function listCities(Request $request, EntityManagerInterface $manager): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class,$city);
        $form ->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()){
                $manager->persist($city);
                $manager->flush();
                return $this->redirectToRoute("listCities");
            }
        }catch (Exception $ex){
            $this->addFlash('error','error');
        }
        $cities = $this->getDoctrine()->getRepository(City::class)->findAll();

        return $this->render('BackOffice/admin/cities/listCities.html.twig', [
            'Cities' => $cities,
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/cities/edit/{id}" , name="editCity")
     * @param City $city
     * @param Request $request
     * @return Response
     */
    public  function editCity(City $city,Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(CityType::class,new $city());
        $form ->handleRequest($request);
        $formEdit = $this->createForm(CityType::class,$city);
        $formEdit ->handleRequest($request);

        try {
            if ($formEdit->isSubmitted() && $formEdit->isValid()){
                $entityManager->flush();
                return $this->redirectToRoute("listCities");
            }
        }catch (Exception $ex){
            $this->addFlash('error','error');
        }

        $Cities = $this->getDoctrine()->getRepository(City::class)->findAll();

        return $this->render('BackOffice/admin/cities/listCities.html.twig', [
            'Cities' => $Cities,
            'form'=>$form->createView(),
            'formEdit'=>$formEdit->createView(),
        ]);
    }


    /**
     * @Route("/city/delete/{id}" , name="deleteCity")
     * @param City $city
     * @return RedirectResponse
     */
    public  function deleteCity(city $city)
    {
        $entityManager = $this->getDoctrine()->getManager();
        // $country = $entityManager->getRepository($city::class)->find($id);
        $entityManager->remove($city);
        $entityManager->flush();

        return $this->redirectToRoute("listCities");
    }
    //</editor-fold>








    //<editor-fold desc="Code travels">
    /**
     * @Route("/travels", name="listTravels")
     * @param Request $request
     * @param TravelRepository $travelRepository
     * @return Response
     */
    public function listTravels(Request $request, TravelRepository $travelRepository): Response
    {

        $travels = $travelRepository->findByAdmin();
        return $this->render('BackOffice/admin/travel/listTravels.html.twig', [
            'travels'=>$travels,
        ]);
    }

    /**
     * @Route("/suggest_travels", name="listSuggestTravels")
     * @param Request $request
     * @param TravelRepository $travelRepository
     * @return Response
     */
    public function listSuggestTravels(Request $request, TravelRepository $travelRepository): Response
    {

        $travels = $travelRepository->findBySuggest();
        return $this->render('BackOffice/admin/travel/listTravelsSuggest.html.twig', [
            'travels'=>$travels,
        ]);
    }

    /**
     * @Route("/travels/add", name="addTravel")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function addTravel(Request $request,ValidatorInterface $validator): Response
    {
        $travel = new Travel();
        $form = $this->createForm(TravelType::class,$travel,['edit' => false]);
        $form ->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){

            $brochureFiles = $form->get('images')->getData();
            if ($brochureFiles) {
                $images = array();
                $countFlashImage = 0;
                /** @var UploadedFile $pic */
                foreach ($brochureFiles as $key=>$file){
                    $assertTravelsImage = new AssertTravelsImage();
                    $assertTravelsImage->setBrochure($file);

                    /** @var ConstraintViolationList $errors */
                    $errors = $validator->validate($assertTravelsImage);
                    if (count($errors) > 0) {
                        //... le cas d'erreur
                        $countFlashImage++;
                        foreach ($errors as $er){
                            $this->addFlash('errorImage', $er->getMessage());
                        }
                    } else {

                        $newFilename = uniqid().'.'.$file->guessExtension();
                        $file->move(
                            $this->getParameter('travel_directory'),
                            $newFilename
                        );

                        if(count($images) < 5){
                            $images[] = $newFilename;
                        }else{break;}

                    }
                }
                $this->addFlash('countFlashImage', $countFlashImage);
                $travel->setImages($images);
            }

            $travel->setStatus(Travel::STATUS_VALIDATED);
            $this->manager->persist($travel);
            $this->manager->flush();

            return $this->redirectToRoute("listTravels");
        }

        return $this->render('BackOffice/admin/travel/addTravel.html.twig', [
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/travels/edit/{id}", name="editTravel")
     * @param Travel $travel
     * @param Request $request
     * @return Response
     */
    public function editTravel(Travel $travel,Request $request): Response
    {
        $form = $this->createForm(TravelType::class,$travel,['edit' => true]);
        $form ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->imageManager->updateTravelImages($request,$travel);
            $this->manager->persist($travel);
            $this->manager->flush();
        }

        return $this->render('BackOffice/admin/travel/editTravel.html.twig', [
            'form'=>$form->createView(),
            'travel' => $travel,
        ]);
    }


    /**
     * @Route("/travel/delete/{id}" , name="deleteTravel")
     * @param Travel $travel
     * @return RedirectResponse
     */
    public function deleteTravel(Travel $travel)
    {
        $this->manager->remove($travel);
        $this->manager->flush();

        return $this->redirectToRoute("listTravels");
    }

    //</editor-fold>


    /**
     * @Route("/customers",name="customers_list")
     * @param Request $request
     *
     * @return Response
     */
    public function customersList(Request $request) {

        $customers = $this->getDoctrine()->getRepository(User::class)->findUserByRole("ROLE_CUSTOMER");
        return $this->render('backOffice/admin/customer/listCustomer.twig', [
            'customers'=>$customers,
        ]);
    }


    /**
     * @Route("/travel/cancel/{id}" , name="cancelTravel")
     *
     * @param Travel $travel
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function cancelTravel(Travel $travel,EntityManagerInterface $manager)
    {

        $travel->setStatus(Travel::STATUS_CANCELED);
        $manager->persist($travel);
        $manager->flush();

        $this->addFlash('success','Order has been Canceled');

        return $this->redirectToRoute('listSuggestTravels');
    }


    /**
     * @Route("/travel/valid/{id}" , name="validTravel")
     *
     * @param Travel $travel
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function validTravel(Travel $travel,EntityManagerInterface $manager)
    {

        $travel->setStatus(Travel::STATUS_VALIDATED);
        $manager->persist($travel);
        $manager->flush();

        $this->addFlash('success','Order has been Canceled');

        return $this->redirectToRoute('listSuggestTravels');
    }

}
