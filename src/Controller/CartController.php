<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Orders;
use App\Entity\Orderproducts;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    /**
     * @Route("api/cart/{productId}", name="cart_add", methods={"POST"})
     */
    public function addProduct(int $productId, Request $request, CartRepository $cartRepository, UserRepository $userRepository, ProductRepository $productRepository, EntityManagerInterface $em) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $userData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$userData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $productData = $productRepository->findOneBy(['id' => $productId]);
        if(!$productData) {
            return $this->json(['error' => 'Product not found'], 404, []);
        }

        $cartData = $cartRepository->findOneBy(['user' => $userData, 'product' => $productData]);
        if(!$cartData) {
            $cart = new Cart();
            $cart->setUser($userData);
            $cart->setProduct($productData);
            $cart->setQuantity(1);

            $em->persist($cart);
        }else{
            $cartData->setQuantity($cartData->getQuantity()+1);
        }
        
        $em->flush();

        return $this->json([], 204, []);
    }

    /**
     * @Route("api/cart/{productId}", name="cart_rm", methods={"DELETE"})
     */
    public function removeProduct(int $productId, Request $request, CartRepository $cartRepository, UserRepository $userRepository, ProductRepository $productRepository, EntityManagerInterface $em) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $userData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$userData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $productData = $productRepository->findOneBy(['id' => $productId]);
        if(!$productData) {
            return $this->json(['error' => 'Product not found'], 404, []);
        }

        $cartData = $cartRepository->findOneBy(['user' => $userData, 'product' => $productData]);
        if(!$cartData) {
            return $this->json(['error' => 'Product not found in cart'], 404, []); //404 ?
        }elseif($cartData->getQuantity() > 1) {
            $cartData->setQuantity($cartData->getQuantity()-1);
        }else{
            $em->remove($cartData);
        }

        $em->flush();

        return $this->json([], 204, []);
    }

    /**
     * @Route("api/cart", name="cart_get", methods={"GET"})
     */
    public function getCart(Request $request, CartRepository $cartRepository, UserRepository $userRepository) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $userData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$userData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $cartData = $cartRepository->findBy(['user' => $headerData]);

        return $this->json($cartData, 200, [], ['groups' => 'cart:read']);
    }

    /**
     * @Route("api/cart/validate/", name="cart_validate", methods={"POST"})
     */
    public function validateCart(Request $request, UserRepository $userRepository, CartRepository $cartRepository, EntityManagerInterface $em) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $userData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$userData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $cartData = $cartRepository->findBy(['user' => $headerData]);

        if(!$cartData) {
            return $this->json(['error' => 'Cart is empty'], 404, []);
        }

        $order = new Orders();
        $order->setUser($userData);

        $totalPrice = 0;
        foreach($cartData as $item) {
            $product = $item->getProduct();
            $quantity = $item->getQuantity();
            $totalPrice += $quantity * $product->getPrice();
        }
        $order->setTotalPrice($totalPrice);

        $order->setCreationDate(new \DateTime());

        foreach($cartData as $item) {
            $orderproduct = new Orderproducts();
            $product = $item->getProduct();
            $quantity = $item->getQuantity();

            $orderproduct->setProducts($product);
            $orderproduct->setQuantity($quantity);
            //setOrders()
            $order->addOrderproduct($orderproduct);

            $em->persist($orderproduct);
        }
        
        $order->setCreationDate(new \DateTime());

        $em->persist($order);

        //empty cart
        foreach($cartData as $item) {
            $em->remove($item);
        }
        $em->flush();

        return $this->json($order, 200, [], ['groups' => 'order:read']);
    }
}

//Si token != userid modifications a effectuer