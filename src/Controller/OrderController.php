<?php

namespace App\Controller;

use App\Repository\OrdersRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/api/orders", name="order_getall", methods={"GET"})
     */
    public function getAllOrder(OrdersRepository $ordersRepository, Request $request, UserRepository $userRepository) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $userData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$userData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        return $this->json($ordersRepository->findBy(['user' => $userData]), 200, [], ['groups' => 'order:read']);
    }

    /**
     * @Route("api/order/{orderId}", name="order_getbyid", methods={"GET"})
     */
    public function getOrderById(int $orderId, OrdersRepository $ordersRepository, Request $request, UserRepository $userRepository) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $userData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$userData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $order = $ordersRepository->findOneBy(['id' => $orderId]);
        if(!$order) {
            return $this->json(['error' => 'Order not found'], 404, []);
        }
        if($order->getUser() != $userData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        return $this->json($order, 200, [], ['groups' => 'order:read']);
    }
}
