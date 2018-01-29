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
     * @Route("/")
     */
	 
    public function indexAction(Request $request, Connection $db)
    {
		$ankiety = [];

		$ankietyNaStrone = 10;
		$ankietyIlosc = $db->query("SELECT COUNT(*) FROM ankiety")->fetch(PDO::FETCH_NUM)[0];
		$strony = ceil($ankietyIlosc / $ankietyNaStrone) - 1;
		if ($strony < 0) $strony = 0;
		
		$strona = (int)$request->query->get('page');
		if ($strona < 0) $strona = 0;
		if ($strona > $strony) $strona = $strony;
		
		$sortBY = (string)$request->query->get('sortby');
		if (empty($sortBY)) $sortBY = 'id';
		
		//Mozliwy sql injection albo wysypanie się apki - zmienna $sortBY
		$stmt = $db->query("SELECT users.name as ankietaUtworzonaPrzez,ankiety.* FROM ankiety 
			JOIN users ON ankiety.uid = users.id ORDER BY " . $sortBY . " LIMIT " . ($ankietyNaStrone * $strona) . ", 10");
		
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) $ankiety[] = $row;
		$stmt->closeCursor();
		
		return $this->render('admin.html.twig', ['ankiety' => $ankiety, 'iloscAnkiet' => $ankietyIlosc ,'sortby' => $sortBY, 'strony' => $strony, 'strona' => $strona]);
    }
	
	/**
    * @Route("/zacznijAnkiete")
    */
	
	public function zacznijAnkiete(Request $request, Connection $db) {
		return $this->render('error.html.twig', ['error' => 'Mam to!']);
	}
}
