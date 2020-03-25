<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\Cart;
use App\Form\CartType;

use App\Entity\Product;
use App\Form\ProductType;

/**
 *  @Route("/{_locale}")
 */

class CartController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $carts = $em->getRepository(Cart::class)->findAll();
        return $this->render('cart/index.html.twig', [
            'carts' => $carts,
            'controller_name' => 'CartController',
        ]);
    }
    
    /**
     * @Route("/cart/delete/{id}", name="delete_cart")
     */
    public function delete(Cart $product = null,  TranslatorInterface $translator)
    {
        if ($product != null) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
            $this->addFlash("success", $translator->trans('flash.cart.delete'));

        } else {
            $this->addFlash("success", $translator->trans('flash.cart.error'));

        }
        return $this->redirectToRoute('home');
    }
}
