<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function index()
    {
        $number = mt_rand(0, 100);
        return $this->render('index.twig', array('number' => $number, 'env' => $_SERVER['APP_ENV']));
    }
}
?>