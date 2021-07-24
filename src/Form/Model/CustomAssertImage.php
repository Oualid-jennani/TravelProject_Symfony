<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;


class CustomAssertImage
{
    /**
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 1600,
     *     minHeight = 200,
     *     maxHeight = 1600,
     *     maxHeightMessage="Please provide an image with max height 1600px",
     *     maxWidthMessage="Please provide an image with max  width 1600px",
     *     minHeightMessage="Please provide an image with min  height 200px",
     *     minWidthMessage="Please provide an image with min  width 200px",
     *     mimeTypes={"image/png", "image/jpeg", "image/jpg"},
     *     mimeTypesMessage="Please provide a valid type: only JPEG and JPG and PNG allowed",
     * )
     */
    private $brochure;


    /**
     * @return mixed
     */
    public function getBrochure()
    {
        return $this->brochure;
    }

    /**
     * @param mixed $brochure
     */
    public function setBrochure($brochure): void
    {
        $this->brochure = $brochure;
    }
}
