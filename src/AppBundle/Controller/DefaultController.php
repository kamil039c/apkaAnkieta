<?php

namespace AppBundle\Controller;

//require("../src/Utils/SimpleFunctions.php");

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Driver\Connection;
//use \App\Utils\SimpleFunctions;
use PDO;

class DefaultController extends Controller
{
	private $dbc;
	private $ankietaPytania = [];
	
	public function __construct() {
		$this->ankietaPytania[] = ['klucz' => "ankietaImie", 'pytanie' => "Podaj swoje imie", 'dbfield' => "imie", 'isint' => false];
		$this->ankietaPytania[] = ['klucz' => "ankietaNazwisko", 'pytanie' => "Podaj swoje nazwisko", 'dbfield' => "nazwisko", 'isint' => false];
		$this->ankietaPytania[] = ['klucz' => "ankietaWiek", 'pytanie' => "Podaj swój wiek", 'dbfield' => "wiek", 'isint' => true];
	}
	
	protected function getUser() {
		if (empty($_SESSION['uid']) || empty($_SESSION['token'])) return null;
		
		$stmt = $this->dbc->prepare("SELECT * FROM users WHERE id = ? AND session_token = ?");
		$stmt->execute([$_SESSION['uid'], $_SESSION['token']]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
		if (!is_array($row)) {
			session_destroy();
			return null;
		}
		
		$stmt->closeCursor();
		return $row;
	}
	
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, Connection $db)
    {
		$dbopts = parse_url('mysql://<user>:<password>@9298bdde-3be9-470c-a64a-b608e625f12a.apkaankieta-4935.mysql.dbs.scalingo.com:31528/apkaankieta_4935?useSSL=true&verifyServerCertificate=false');
		//return $this->render('error.html.twig', ['error' => print_r($dbopts, true)]);
		
		$queries = [
			"CREATE TABLE `ankiety` (
				`id` int(11) NOT NULL,
				`uid` int(11) NOT NULL,
				`imie` varchar(50) NOT NULL,
				`nazwisko` varchar(50) NOT NULL,
				`wiek` int(11) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf-8",
			"CREATE TABLE `ankiety` (
				`id` int(11) NOT NULL,
				`uid` int(11) NOT NULL,
				`imie` varchar(50) NOT NULL,
				`nazwisko` varchar(50) NOT NULL,
				`wiek` int(11) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf-8",
			"INSERT INTO users(name,pwd) VALUES('kamil', '" . $pwd . "')",
			"INSERT INTO users(name,pwd) VALUES('franek', '" . $pwd . "')",
			"INSERT INTO users(name,pwd) VALUES('marian', '" . $pwd . "')",
			"INSERT INTO users(name,pwd) VALUES('malgoska', '" . $pwd . "')",
			"INSERT INTO users(name,pwd) VALUES('kaska', '" . $pwd . "')",
			"INSERT INTO users(name,pwd) VALUES('mateusz', '" . $pwd . "')",
			"INSERT INTO users(name,pwd) VALUES('adam', '" . $pwd . "')"
		];
		
		foreach ($queries as $query) {
			try {
				$db->exec($query);
			} catch (PDOException $Exception) {
				return $this->render('error.html.twig', ['error' =>  $Exception->getMessage( ) ]);
			}
		}
		
		return $this->render('error.html.twig', ['error' =>  'tu jestem' ]);
		
		//return $this->render('error.html.twig', ['error' => print_r($dbopts, true)]);
		$this->dbc = $db;
		$ankietaFaza = 0;
		$ankieta = null;
		$ankiety = [];
		
		if (($zalogowanyUser = $this->getUser()) != null) {
			$ankietaFaza = (int)$_SESSION['ankietaFaza'];
			if (isset($this->ankietaPytania[$ankietaFaza - 1])) $ankieta = $this->ankietaPytania[$ankietaFaza - 1];
			
			$stmt = $db->prepare("SELECT * FROM ankiety WHERE uid = ?");
			$stmt->execute([$zalogowanyUser['id']]);
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) $ankiety[] = $row;
			$stmt->closeCursor();
		}
		
        return $this->render(
			'default/index.html.twig', 
			[
				'user' => $zalogowanyUser, 'ankietaFaza' => $ankietaFaza, 'ankieta' => $ankieta, 
				'iloscPytan' => count($this->ankietaPytania), 'ankiety' => $ankiety
			]
		);
    }
	
	/**
    * @Route("/login", methods="POST")
    */
	
	public function login(Request $request, Connection $db) {
		if (empty($request->request->get('name')) || empty($request->request->get('pwd'))) {
			return $this->render('error.html.twig', ['error' => 'Wprowadź login i hasło']);
		}
		
		$stmt = $db->prepare("SELECT * FROM users WHERE name = ?");
		$stmt->execute([$request->request->get('name')]);
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		
		if (!is_array($row)) {
			return $this->render(
				'error.html.twig', 
				['error' => 'User o nazwie "' . $request->request->get('name') . '" nie istnieje!']
			);
		}
		
		if (!password_verify($request->request->get('pwd'), $row['pwd'])) {
			return $this->render('error.html.twig', ['error' => 'Autentykacja nie powiodła się!']);
		}
		
		session_start();
		
		$_SESSION['uid'] = $row['id'];
		$_SESSION['token'] = $row['session_token'];
		$_SESSION['ankietaFaza'] = 0;
		
		return $this->redirectToRoute('homepage', array(), 301);
		
		/*return new Response(
			$request->request->get('name'),
			Response::HTTP_OK,
			array('content-type' => 'text/html')
		);*/
	}
	
	/**
    * @Route("/logout")
    */
	
	public function logout(Request $request) {
		session_destroy();
		return $this->redirectToRoute('homepage', array(), 301);
	}
	
	/**
    * @Route("/zacznijAnkiete")
    */
	
	public function zacznijAnkiete(Request $request, Connection $db) {
		$this->dbc = $db;
		if ($this->getUser() == null) {
			return $this->render('error.html.twig', ['error' => 'Brak autoryzacji!']);
		}
		
		if ($_SESSION['ankietaFaza'] != 0) {
			return $this->render('error.html.twig', ['error' => 'Ankieta została rozpoczęta!']);
		}
		
		$_SESSION['ankietaFaza'] = 1;
		return $this->redirectToRoute('homepage', array(), 301);
	}
	
	/**
    * @Route("/przerwijAnkiete")
    */
	
	public function przerwijAnkiete(Request $request, Connection $db) {
		$this->dbc = $db;
		if ($this->getUser() == null) {
			return $this->render('error.html.twig', ['error' => 'Brak autoryzacji!']);
		}
		
		if ($_SESSION['ankietaFaza'] == 0) {
			return $this->render('error.html.twig', ['error' => 'Ankieta nie została rozpoczęta!']);
		}
		
		$_SESSION['ankietaFaza'] = 0;
		foreach($this->ankietaPytania as $pytanie) $_SESSION[$pytanie['klucz']] = "";
		return $this->redirectToRoute('homepage', array(), 301);
	}
	
	/**
    * @Route("/kontynuujAnkiete", methods="POST")
    */
	
	public function kontynuujAnkiete(Request $request, Connection $db) {
		$this->dbc = $db;
		if (($user = $this->getUser()) == null) {
			return $this->render('error.html.twig', ['error' => 'Brak autoryzacji!']);
		}
		
		if ($_SESSION['ankietaFaza'] == 0) {
			return $this->render('error.html.twig', ['error' => 'Ankieta nie została rozpoczęta!']);
		}
		
		if ($_SESSION['ankietaFaza'] == 4) {
			$_SESSION['ankietaFaza'] = 0;
			$keys = [];
			$values = [$user['id']];
			
			foreach($this->ankietaPytania as $pytanie) {
				$keys[] = $pytanie['dbfield'];
				$values[] = $pytanie['isint'] ? (int)$_SESSION[$pytanie['klucz']] : (string)$_SESSION[$pytanie['klucz']];
				$_SESSION[$pytanie['klucz']] = "";
			}
			
			$stmt = $db->prepare("INSERT INTO ankiety(uid," . implode(',',$keys) 
				. ") VALUES(" . str_repeat('?,',count($keys)) . "?)");
			$stmt->execute($values);
			$stmt->closeCursor();
			
			return $this->redirectToRoute('homepage', array(), 301);
		}
		
		if (empty($request->request->get('text'))) {
			return $this->render('error.html.twig', ['error' => 'Musisz coś wpisać, aby kontynuować']);
		}
		
		$_SESSION[$this->ankietaPytania[$_SESSION['ankietaFaza'] - 1]['klucz']] = $request->request->get('text');
		$_SESSION['ankietaFaza']++;
		
		return $this->redirectToRoute('homepage', array(), 301);
	}
}
