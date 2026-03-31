<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;

use App\Entity\OrderProducts;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{    
     public function __construct(private MailerInterface $mailer){
    }


    #[Route('/order', name: 'app_order')]
    public function index(Request $request, SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManager, Cart $cart, Security $security): Response
    {   
        $data=$cart->getCart($session);
        $user=$security->getUser();       

        if (!$user instanceof \App\Entity\User) {
              return $this->redirectToRoute('app_login');
         }

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);       
        if($form->isSubmitted() && $form->isValid()){
           $paymentMethod = $form->get('paymentMethod')->getData();

        if (!empty($data['total'])) {

            
            $order->setTotalPrice($data['total']);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setUser($user);

            $order->setFirstName($user->getFirstName());
            $order->setLastName($user->getLastName());
            $order->setEmail($user->getEmail());
            $order->setTelephoneNumber($user->getTelephoneNumber());
            $order->setAddress($user->getAddress());
            $order->setCity($user->getCity());

            $entityManager->persist($order);

            foreach ($data['cart'] as $value) {
                $orderProduct = new OrderProducts();
                $orderProduct->setOrder($order);
                $orderProduct->setProduct($value['product']);
                $orderProduct->setQuantity($value['quantity']);
                $entityManager->persist($orderProduct);
            }

            $entityManager->flush();

         
            if ($paymentMethod === 'cash_split') {

                // ✔️ paiement en boutique
                $session->set('cart', []);

                $html = $this->renderView('mail/orderConfirm.html.twig', [
                    'order' => $order
                ]);

                $email = (new Email())
                    ->from('izaberu.creations@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Confirmation de commande')
                    ->html($html);

                $this->mailer->send($email);

                return $this->redirectToRoute('app_order_message');
            }

            if ($paymentMethod === 'stripe') {
              
                return $this->redirectToRoute('app_stripe_checkout', [
                    'id' => $order->getId()
                ]);
            }
        }
    }

    return $this->render('order/index.html.twig', [
        'form' => $form->createView(),
        'total' => $data['total']
    ]);
    }


        #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status'=>200, "message"=>'on', 'content'=> $cityShippingPrice]));
    }


     #[Route('/order_message', name:'app_order_message')]
     public function orderMessage():Response
     {
        return $this->render('order/order_message.html.twig');
     }

#[Route('/orders', name:'app_orders_show')]
public function getAllOrdes(OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request):Response
{    
            $data=$orderRepository->findAll();
            $orders = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            1
        );
    
    return $this->render('order/order.html.twig', [
        'orders'=>$orders
    ]);
}

#[Route('/order/{id}/is-completed/update', name:'app_orders_is-completed-update')]

public function isCompletedUpdate($id,OrderRepository $orderRepository, EntityManagerInterface $entityManager):Response
{ 
        $order = $orderRepository->find($id);
        $order->setIsCompleted(!$order->isCompleted());
        $entityManager->flush();
              

        return $this->redirectToRoute('app_orders_show');

}

#[Route('/order/{id}/remove', name:'app_orders_remove')]
public function removeOrder(Order $order, EntityManagerInterface $entityManager):Response

{
    $entityManager->remove($order);
    $entityManager->flush();
    return $this->redirectToRoute('app_orders_show');
}



}
