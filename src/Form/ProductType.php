<?php


namespace App\Form;
use App\Entity\Category;
use App\Entity\Condition;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['entityManager'];

        $builder
            ->add('name', TextType::class, [
                'attr' => ['autofocus' => true],
                'label' => 'Name'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantity'
            ])
            ->add('price', NumberType::class, [
                'scale' => 2,
                'label' => 'Price (EUR)'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => function ($category) {
                    return $category->getName();
                }
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (jpg/jpeg or png file, leaving this empty will not interfere with existing image)',
                'mapped' => false,
                'required' => false,

                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid .jpeg or .png image',
                    ])
                ],
            ])
            ->add('proteinPercent', PercentType::class, [

                'label' => 'Protein'
            ])
            ->add('carbohydratePercent', PercentType::class, [

                'label' => 'Carbohydrate'
            ])
            ->add('lipidPercent', PercentType::class, [

                'label' => 'Lipid'
            ])
            ->add('conditions', EntityType::class, array(
                'class' => Condition::class,
                'multiple' => true,
                'required' => false,
                'label' => 'If your product contains any of the ingredients below, please check them',
                'expanded' => true,
                'choice_label' => function ($condition) {
                    return $condition->getDescription();
                    }
            ))
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => array('class' => 'btn btn-default')
            ])

        ;


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => Product::class,
        ));

        $resolver->setRequired('user');
        $resolver->setAllowedTypes('user', array(User::class, 'int'));
        $resolver->setRequired('entityManager');
        $resolver->setRequired('product');

    }

}