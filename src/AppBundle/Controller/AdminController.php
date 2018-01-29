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
		
		/*
		ZEstaw testowy
		$imiona = ["mariusz","mateusz","franio","bogusław","mietek","wiesio","łukasz","sebastian"];
		$nazwiska = ["kowalski","nowak","cieplak","marczuk","kowalik","niegłowski","ćwierkacz"];
		
		for ($i = 0; $i < 50; $i++) {
			$db->query("INSERT INTO ankiety(uid,imie,nazwisko,wiek) VALUES("
				.mt_rand(1,7).",'".$imiona[mt_rand(0, count($imiona) - 1)]."','".$nazwiska[mt_rand(0, count($nazwiska) - 1)]."',".mt_rand(17,66).")");
		}*/
		
		
		
		
		//Mozliwy sql injection albo wysypanie się apki - zmienna $sortBY
		$stmt = $db->query("SELECT users.name as ankietaUtworzonaPrzez,ankiety.* FROM ankiety 
			JOIN users ON ankiety.uid = users.id ORDER BY " . $sortBY . " LIMIT " . ($ankietyNaStrone * $strona) . ", 10");
		
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) $ankiety[] = $row;
		$stmt->closeCursor();
		
		/*return new Response(
			print_r($request,true),
			Response::HTTP_OK,
			array('content-type' => 'text/html')
		);*/
		
		return $this->render('admin.html.twig', ['ankiety' => $ankiety, 'iloscAnkiet' => $ankietyIlosc ,'sortby' => $sortBY, 'strony' => $strony, 'strona' => $strona]);
    }
	
	/**
    * @Route("/zacznijAnkiete")
    */
	
	public function zacznijAnkiete(Request $request, Connection $db) {
		return $this->render('error.html.twig', ['error' => 'Mam to!']);
	}
}
