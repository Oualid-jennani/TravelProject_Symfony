<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Travel;
use App\Repository\CityRepository;
use App\Repository\TravelRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TravelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $edit = $options['edit'];

        if ($edit == false) {
            $builder
                ->add('images', FileType::class, [
                    'attr' =>
                    [
                        'label' => 'Brochure (Image file)',
                        'accept' => 'image/*'
                    ],

                    'required' => false,
                    'multiple' => true
                ]);
        }


        $builder
            ->add('start', EntityType::class, [
                'class' => City::class,
                'required' => true,
                'query_builder' => function (CityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'Chose a city'

            ])
            ->add('finish', EntityType::class, [
                'class' => City::class,
                'required' => true,
                'query_builder' => function (CityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'Chose a city'
            ])
            ->add('startDate', DateType::class)
            ->add('placeNumber', IntegerType::class)
            ->add('description')
            ->add('price');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Travel::class,
            'validation_groups' => false,
        ]);
        $resolver->setRequired([
            'edit',
        ]);
    }
}
