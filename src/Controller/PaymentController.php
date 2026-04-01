<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
#[Route('/payment/cash/{id}/add', name: 'app_payment_cash_add', methods: ['POST'])]
public function addCashPayment(Request $request, Order $order, EntityManagerInterface $em, PaymentRepository $paymentRepo): Response
{
    $amount = (float) $request->request->get('amount');

    $payment = new Payment();
    $payment->setOrderRef($order);
    $payment->setMethod('cash_split');
    $payment->setAmount($amount);
    $payment->setCreatedAt(new \DateTimeImmutable());
    $paidTotal = $paymentRepo->sumAmounts($order);
    
    if ($order->getTotalPrice() <= $amount + $paidTotal) {
        $payment->setIsCompleted(true);
    } else {
        $payment->setIsCompleted(false);
    }
    $em->persist($payment);
    $em->flush();

    $this->addFlash('success', 'Paiement enregistré !');

    return $this->redirectToRoute('app_payment_cash_summary', [
    'id' => $order->getId()]);
}

#[Route('/payment/cash/{id}/summary', name: 'app_payment_cash_summary')]
public function summary(Order $order, PaymentRepository $paymentRepository): Response
{
    // Récupérer tous les paiements de cette commande
    $payments = $paymentRepository->findBy(['orderRef' => $order]);

    // Calculer le total payé
    $paymentsTotal = $paymentRepository->sumAmounts($order);

    return $this->render('payment/summary.html.twig', [
        'order' => $order,
        'payments' => $payments,
        'paymentsTotal' => $paymentsTotal,
    ]);
}



}
