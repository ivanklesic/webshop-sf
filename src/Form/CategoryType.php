<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['entityManager'];

        $builder
            ->add('name', TextType::class, [
                'attr' => ['autofocus' => true],
                'label' => 'Name'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => array('class' => 'btn btn-default')
            ])
        ;


    }

}