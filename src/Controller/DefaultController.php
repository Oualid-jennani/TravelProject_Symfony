<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Reserve;
use App\Entity\Travel;
use App\Entity\User;
use App\Form\Model\AssertTravelsImage;
use App\Form\ReserveType;
use App\Form\SearchTravelType;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * StoreController constructor.
     * @param Security $security
     * @param SessionInterface $session
     * @param EntityManagerInterface $manager
     */
    public function __construct(Security $security, SessionInterface $session, EntityManagerInterface $manager)
    {
        $this->security = $security;
        $this->session = $session;
        $this->manager = $manager;
    }

    //<editor-fold desc="Code index">

    /**
     * @Route("/", name="index")
     *
     * @param Request $request
     * @param TravelRepository $travelRepository
     * @return Response the response
     */
    public function index(Request $request, TravelRepository $travelRepository): Response
    {
        $travels = $travelRepository->findAll();
        $travel = new Travel();
        $form = $this->createForm(SearchTravelType::class, $travel);
        $form->handleRequest($request);

        $listTravel = "Default";

        if ($form->isSubmitted() && $form->isValid()) {

            $listTravel = "Search";

            $travels = $travelRepository->findBy([
                'start' => $travel->getStart(),
                'finish' => $travel->getFinish(),
                'startDate' => $travel->getStartDate(),

            ]);
        }

        return $this->render('frontOffice/default/index.html.twig', [
            'form' => $form->createView(),
            'listTravel' => $listTravel,
            'travel' => $travel,
            'travels' => $travels,
        ]);
    }




    /**
     * @Route("/suggestTravel", name="suggestTravel")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function suggestTravel(Request $request, ValidatorInterface $validator): Response
    {
        $travel = new Travel();
        $form = $this->createForm(TravelType::class, $travel, ['edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $brochureFiles = $form->get('images')->getData();
            if ($brochureFiles) {
                $images = array();
                $countFlashImage = 0;
                /** @var UploadedFile $pic */
                foreach ($brochureFiles as $key => $file) {
                    $assertTravelsImage = new AssertTravelsImage();
                    $assertTravelsImage->setBrochure($file);

                    /** @var ConstraintViolationList $errors */
                    $errors = $validator->validate($assertTravelsImage);
                    if (count($errors) > 0) {
                        //... le cas d'erreur
                        $countFlashImage++;
                        foreach ($errors as $er) {
                            $this->addFlash('errorImage', $er->getMessage());
                        }
                    } else {

                        $newFilename = uniqid() . '.' . $file->guessExtension();
                        $file->move(
                            $this->getParameter('travel_directory'),
                            $newFilename
                        );

                        if (count($images) < 5) {
                            $images[] = $newFilename;
                        } else {
                            break;
                        }
                    }
                }
                $this->addFlash('countFlashImage', $countFlashImage);
                $travel->setImages($images);
            }

            /** @var User $user */
            $user = $this->getUser();
            $travel->setUser($user);
            $travel->setStatus(Travel::STATUS_INITIATED);
            $this->manager->persist($travel);
            $this->manager->flush();

            return $this->render('frontOffice/default/suggestComplete.html.twig');
        }

        return $this->render('frontOffice/default/SuggestTravel.html.twig', [
            'form' => $form->createView(),
        ]);
    }




    //</editor-fold>

    /**
     * @Route("/detailTravel/{id}", name="detailTravel")
     *
     * @param Travel $travel
     * @param Request $request
     * @return Response the response
     */
    public function show_travel(Travel $travel, Request $request): Response
    {
        return $this->render('frontOffice/default/Reserve.html.twig', [
            'travel' => $travel,
        ]);
    }




    /**
     * @Route("/Reserve/{id}", name="Reserve")
     *
     * @param Travel $travel
     * @param Request $request
     * @return Response the response
     */
    public function Reserve(Travel $travel, Request $request): Response
    {

        $reserve = new Reserve();
        $form = $this->createForm(ReserveType::class, $reserve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $reserve->setClient($user);
            $reserve->setTravel($travel);
            $this->manager->persist($reserve);
            $this->manager->flush();
        }

        return $this->render('frontOffice/default/Reserve.html.twig', [
            'travel' => $travel,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/about", name="about")
     * @param Request $request
     * @return Response the response
     */
    public function about(Request $request): Response
    {
        return $this->render('frontOffice/default/aboutUs.html.twig', []);
    }
}
