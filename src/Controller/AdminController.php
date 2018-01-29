<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Driver\Connection;
use PDO;

/**
 * @Route("/admin")
 */

class AdminController extends Controller
{
	private $dbc;
	
    /**
     * @Route("/", name="homepage")
     */
	 
    public function indexAction(Request $request, Connection $db)
    {
		return $this->render('error.html.twig', ['error' => 'Mam to!']);
    }
	
	/**
    * @Route("/zacznijAnkiete")
    */
	
	public function zacznijAnkiete(Request $request, Connection $db) {
		return $this->render('error.html.twig', ['error' => 'Mam to!']);
	}
	
}
