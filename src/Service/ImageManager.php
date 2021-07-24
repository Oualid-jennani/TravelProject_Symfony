<?php
namespace App\Service;

use App\Entity\Travel;
use App\Form\Model\CustomAssertImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ImageManager
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var array
     */
    private $newImages;

    /**
     * @var FlashBagInterface
     */
    private $flash;


    /**
     * Service Images Constructor.
     * @param Security $security
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     * @param FlashBagInterface $flash
     */
    public function __construct(
        Security $security,
        EntityManagerInterface $manager,
        ValidatorInterface $validator,
        FlashBagInterface $flash
    ) {
        $this->security = $security;
        $this->manager = $manager;
        $this->validator = $validator;
        $this->flash = $flash;
        $this->newImages = [];
        $this->fileSystem = new Filesystem();
    }


    public function updateTravelImages(Request $request,Travel $travel)
    {
        $url = 'images/travel';
        $oldImagesRequest = (array)$request->request->get("oldPic");
        $oldImagesProduct = $travel->getImages();
        $newImagesRequest = $request->files->get('pic');

        $this->createFolderUrl($url);
        $this->removeOldImages($oldImagesProduct,$oldImagesRequest,$url);
        $this->saveImages($newImagesRequest,new CustomAssertImage(),$url);

        $travel->setImages($this->newImages);
        $this->manager->persist($travel);
        $this->manager->flush();
    }

    public function createFolderUrl($url)
    {
        if(false === $this->fileSystem->exists($url))
        {
            $this->fileSystem->mkdir($url);
        }
    }

    public function removeOldImages($currantImages,$imagesRequest,$url)
    {
        foreach($currantImages as $src){
            if (!\in_array($src, $imagesRequest)) {
                $this->fileSystem->remove([$url.'/'.$src]);
            } else {
                if(count($this->newImages) < 5){
                    $this->newImages[] = $src;
                }
            }
        }
    }

    public function saveImages($pictures,$assertImage,$url)
    {
        $countFlashImage = 0;
        /** @var UploadedFile $pic */
        foreach($pictures as $pic){
            $assertImage->setBrochure($pic);
            /** @var ConstraintViolationList $errors */
            $errors = $this->validator->validate($assertImage);
            if (count($errors) > 0) {
                $countFlashImage++;
                foreach ($errors as $er){
                    $this->flash->add('errorImage', $er->getMessage());
                }
            } else {
                if('' !== $path = $pic->getClientOriginalName()){
                    $ext = \pathinfo($path, PATHINFO_EXTENSION);
                    $src = \uniqid().'.'.$ext;
                    $array[] = $src;
                    $pic->move($url, $src);
                    if(count($this->newImages) < 5){
                        $this->newImages[] = $src;
                    }else{break;}
                }
            }
        }
        $this->flash->add('countFlashImage', $countFlashImage);
    }

}