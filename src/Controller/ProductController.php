<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\Product;
use App\Form\ProductType;

use App\Entity\Cart;
use App\Form\CartType;

/**
 *  @Route("/{_locale}")
 */

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(Request $request,  TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('picture')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('upload_dir'),
                        $fileName
                    );
                } catch (FileException $e) {
                    return $this->redirectToRoute('home');
                }

                $product->setPicture($fileName);
            }

            $em->persist($product);
            $em->flush();
            $this->addFlash("success", $translator->trans('flash.product.added'));
        }
    
        $products = $em->getRepository(product::class)->findAll();

        return $this->render('product/index.html.twig', [
            
            'products' => $products,
            'form_add_product' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}", name="selected_product")
     */
    public function cart(Request $request ,Product $product = null,  TranslatorInterface $translator)
    {
        if($product != null){

        $em = $this->getDoctrine()->getManager();

        $cart = new Cart($product);
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($product->getQuantity() >= $cart->getQuantity() ){
            $cart->setDateAdd(new \DateTime());
            $cart->setState(false);
            $cart->setProduct($product);
            $em->persist($cart);
            $em->flush();

            $this->addFlash('success',  $translator->trans('flash.product.addCart'));

            }else {

                $this->addFlash('danger',  $translator->trans('flash.product.errorAddCart'));
            }
        }
        $carts = $em->getRepository(Cart::class)->findAll();
        return $this->render('product/product.html.twig', [
            'product' => $product,
            'form_add_cart' => $form->createView(),
        ]);
    }
    }

    /**
     * @Route("/product/delete/{id}", name="delete_product")
     */
    public function delete(Product $product = null,  TranslatorInterface $translator)
    {
        if ($product != null) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();

            if ($product->getPicture() != null) {
                unlink($this->getParameter('upload_dir') . $product->getPicture());
            }

            $this->addFlash('success',  $translator->trans('flash.product.delete'));

        } else {
            $this->addFlash('danger',  $translator->trans('flash.product.retry'));
        }
        return $this->redirectToRoute('product');
    }
}
