<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/orders", name="app_order")
     */
    public function index(): Response
    {
        // Retrieve all orders from the database
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/order/create", name="create_order")
     */
    public function createOrder(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            $this->addFlash('success', 'Order created successfully');

            return $this->redirectToRoute('app_order'); // Replace with your order listing route
        }

        return $this->render('order/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
