<?php


namespace App\Security;


use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProductVoter extends Voter
{

    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const VIEW = 'view';

    protected function supports($attribute, $subject)
    {

        if (!in_array($attribute, [self::CREATE, self::EDIT, self::DELETE, self::VIEW])) {
            return false;
        }


        if (!$subject instanceof Product) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }


        /** @var Product $product */
        $product = $subject;

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($user);
            case self::EDIT:
                return $this->canEdit($product, $user);
            case self::DELETE:
                return $this->canDelete($product, $user);
            case self::VIEW:
                return $this->canView($product, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate(User $user)
    {
        if(in_array('ROLE_SELLER', $user->getRoles())){
            return true;
        }

        return false;
    }

    private function canEdit(Product $product, User $user)
    {
        if(in_array('ROLE_ADMIN', $user->getRoles())){
            return true;
        }

        return $user === $product->getSeller();
    }

    private function canDelete(Product $product, User $user)
    {
        return $this->canEdit($product, $user);
    }

    private function canView(User $user)
    {
        if(in_array('ROLE_CUSTOMER', $user->getRoles())){
            return true;
        }
        return false;
    }

}