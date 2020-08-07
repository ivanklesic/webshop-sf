<?php


namespace App\Form;


use App\Entity\Diet;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DietType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['autofocus' => true],
                'label' => 'Name'
            ])
            ->add('description', TextType::class, [
                'attr' => ['autofocus' => true],
                'label' => 'List foods or ingredients that are harmful for this diet'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => array('class' => 'btn btn-default')
            ])
            ->add('proteinPercent', IntegerType::class, [

                'label' => 'Protein percent in macronutrient ratio'
            ])
            ->add('carbohydratePercent', IntegerType::class, [

                'label' => 'Carbohydrate percent in macronutrient ratio'
            ])
            ->add('lipidPercent', IntegerType::class, [

                'label' => 'Lipid percent in macronutrient ratio'
            ])
        ;


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => Diet::class,
        ));

        $resolver->setRequired('user');
        $resolver->setAllowedTypes('user', array(User::class, 'int'));
        $resolver->setRequired('diet');

    }

}