<?php

namespace App\Form;


use App\Entity\Condition;
use App\Entity\Diet;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;




class UserType  extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $requiredPassword = $options['requiredPassword'];
        /** @var User $user */
        $user = $options['user'];

        $roles = array();
        $roles['Customer'] = 'ROLE_CUSTOMER';
        $roles['Seller'] = 'ROLE_SELLER';



        $builder
            ->add('username', TextType::class , array(
                'required' => true,
                'trim' => true,
                'label' => 'Username'))
            ->add('password', RepeatedType::class, array(
                'required' => $requiredPassword,
                'empty_data' => '',
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password')
            ))
            ->add('firstname', TextType::class , array(
                'required' => true,
                'trim' => true,
                'label' => 'First name'))
            ->add('lastname', TextType::class , array(
                'required' => true,
                'trim' => true,
                'label' => 'Last name'));

        if($requiredPassword){
            $builder
                ->add('roles', ChoiceType::class, array(
                    'choices'  => $roles,
                    'label' => 'Role',
                    'multiple' => false))
                ->add('activeDiet', EntityType::class, array(
                    'choices'  => Diet::class,
                    'label' => 'If you want to get recommendations based on your diet, select one of the following options.',
                    'multiple' => false,
                    'required' => false
                ))
                ->add('conditions', EntityType::class, array(
                    'class' => Condition::class,
                    'multiple' => true,
                    'required' => false,
                    'label' => 'Select medical conditions which affect you. This will be used to generate warnings on checkout',
                    'expanded' => true
                ));

        }else{
            if(in_array('ROLE_CUSTOMER', $user->getRoles())){
                $builder
                    ->add('activeDiet', EntityType::class, array(
                        'choices'  => Diet::class,
                        'label' => 'If you want to get recommendations based on your diet, select one of the following options.',
                        'multiple' => false,
                        'required' => false
                    ))
                    ->add('conditions', EntityType::class, array(
                        'class' => Condition::class,
                        'multiple' => true,
                        'required' => false,
                        'label' => 'Select medical conditions which affect you. This will be used to generate warnings on checkout',
                        'expanded' => true
                    ));
            }
        }

        $builder
            ->add('save', SubmitType::class, array(
                'attr' => array('class' => 'save'),
                'label' => 'Submit'))
        ;

        if($requiredPassword){
            $builder->get('roles')
                ->addModelTransformer(new CallbackTransformer(
                    function ($rolesArray) {
                        // transform the array to a string
                        return count($rolesArray)? $rolesArray[0]: null;
                    },
                    function ($rolesString) {
                        // transform the string back to an array
                        return [$rolesString];
                    }
                ));
        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('user');
        $resolver->setAllowedTypes('user', array(User::class, 'int'));
        $resolver->setRequired('requiredPassword');

    }
}