<?php
declare(strict_types=1);

require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/PointageController.php';
require_once __DIR__ . '/controllers/InscriptionController.php';
require_once __DIR__ . '/controllers/BoutiqueController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/AdminCmsController.php';
require_once __DIR__ . '/controllers/MembreController.php';
require_once __DIR__ . '/controllers/AdminEvenementController.php';
require_once __DIR__ . '/controllers/AdminLiaisonController.php';
require_once __DIR__ . '/controllers/AdminBoutiqueController.php';
require_once __DIR__ . '/controllers/AdminCatalogueController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && $scriptName !== '\\') {
    $requestUri = (string) substr($requestUri, strlen($scriptName));
}

if ($requestUri === '') {
    $requestUri = '/';
}

switch ($requestUri) {
    // ... ROUTES EXISTANTES ...
    case '/':
    case '/index.php':
        if ($requestMethod === 'GET') (new HomeController())->index();
        else retournerErreur405();
        break;

    case '/seances':
        if ($requestMethod === 'GET') (new PointageController())->index();
        else retournerErreur405();
        break;

    case '/pointage':
        if ($requestMethod === 'POST') (new PointageController())->updatePointage();
        else retournerErreur405(true);
        break;
        
    case '/pointage/search':
        if ($requestMethod === 'GET') (new PointageController())->searchHorsCreneau();
        else retournerErreur405();
        break;

    case '/inscription':
        if ($requestMethod === 'GET') (new InscriptionController())->index();
        elseif ($requestMethod === 'POST') (new InscriptionController())->process();
        else retournerErreur405();
        break;

    case '/boutique':
        if ($requestMethod === 'GET') (new BoutiqueController())->index();
        else retournerErreur405();
        break;
        
    case '/boutique/checkout':
        if ($requestMethod === 'POST') (new BoutiqueController())->processCheckout();
        else retournerErreur405(true);
        break;

    case '/mes-commandes':
        if ($requestMethod === 'GET') (new BoutiqueController())->mesCommandes();
        else retournerErreur405();
        break;

    case '/admin/login':
        if ($requestMethod === 'GET') (new AdminController())->login();
        else retournerErreur405();
        break;

    case '/admin/login/process':
        if ($requestMethod === 'POST') (new AdminController())->processLogin();
        else retournerErreur405(true);
        break;

    case '/admin/logout':
        if ($requestMethod === 'GET') (new AdminController())->logout();
        else retournerErreur405();
        break;

    case '/admin':
        if ($requestMethod === 'GET') (new AdminController())->dashboard();
        else retournerErreur405();
        break;

    case '/admin/valider-paiement':
        if ($requestMethod === 'POST') (new AdminController())->processValidationPaiement();
        else retournerErreur405(true);
        break;

    case '/admin/parametres':
        if ($requestMethod === 'POST') (new AdminController())->processUpdateParametres();
        else retournerErreur405(true);
        break;

    case '/admin/cms':
        if ($requestMethod === 'GET') (new AdminCmsController())->index();
        else retournerErreur405();
        break;

    case '/admin/cms/builder':
        if ($requestMethod === 'GET') (new AdminCmsController())->builder();
        else retournerErreur405();
        break;

    case '/admin/cms/save':
        if ($requestMethod === 'POST') (new AdminCmsController())->save();
        else retournerErreur405(true);
        break;

    case '/admin/cms/upload':
        if ($requestMethod === 'POST') (new AdminCmsController())->upload();
        else retournerErreur405(true);
        break;

    case '/admin/cms/delete':
        if ($requestMethod === 'POST') (new AdminCmsController())->delete();
        else retournerErreur405(true);
        break;

    case '/espace-famille':
        if ($requestMethod === 'GET') (new MembreController())->dashboard();
        else retournerErreur405();
        break;

    case '/espace-famille/update':
        if ($requestMethod === 'POST') (new MembreController())->updateInfo();
        else retournerErreur405(true);
        break;

    case '/espace-famille/participation':
        if ($requestMethod === 'POST') (new MembreController())->updateParticipation();
        else retournerErreur405(true);
        break;

    case '/admin/evenements':
        if ($requestMethod === 'GET') (new AdminEvenementController())->index();
        else retournerErreur405();
        break;

    case '/admin/evenements/save':
        if ($requestMethod === 'POST') (new AdminEvenementController())->processSave();
        else retournerErreur405(true);
        break;

    case '/admin/evenements/delete':
        if ($requestMethod === 'POST') (new AdminEvenementController())->processDelete();
        else retournerErreur405(true);
        break;

    // ----- NOUVELLES ROUTES ÉVÉNEMENTS (Recherche & Ajout manuel) -----
    case '/admin/evenements/search':
        if ($requestMethod === 'GET') (new AdminEvenementController())->searchAdherent();
        else retournerErreur405();
        break;

    case '/admin/evenements/participant/add':
        if ($requestMethod === 'POST') (new AdminEvenementController())->addParticipant();
        else retournerErreur405(true);
        break;

    case '/admin/liaisons':
        if ($requestMethod === 'GET') (new AdminLiaisonController())->index();
        else retournerErreur405();
        break;

    case '/admin/liaisons/add':
        if ($requestMethod === 'POST') (new AdminLiaisonController())->processAdd();
        else retournerErreur405(true);
        break;

    case '/admin/liaisons/delete':
        if ($requestMethod === 'POST') (new AdminLiaisonController())->processDelete();
        else retournerErreur405(true);
        break;

    case '/admin/boutique':
        if ($requestMethod === 'GET') (new AdminBoutiqueController())->index();
        else retournerErreur405();
        break;

    case '/admin/boutique/statut':
        if ($requestMethod === 'POST') (new AdminBoutiqueController())->processStatutUpdate();
        else retournerErreur405(true);
        break;

    case '/admin/boutique/stock':
        if ($requestMethod === 'POST') (new AdminBoutiqueController())->processStockUpdate();
        else retournerErreur405(true);
        break;

    case '/admin/catalogue':
        if ($requestMethod === 'GET') (new AdminCatalogueController())->index();
        else retournerErreur405();
        break;

    case '/admin/catalogue/save':
        if ($requestMethod === 'POST') (new AdminCatalogueController())->processSave();
        else retournerErreur405(true);
        break;

    case '/admin/catalogue/delete':
        if ($requestMethod === 'POST') (new AdminCatalogueController())->processDelete();
        else retournerErreur405(true);
        break;

    default:
        http_response_code(404);
        echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>Erreur 404</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100 flex items-center justify-center h-screen'><div class='text-center'><h1 class='text-6xl font-bold text-blue-700'>404</h1><p class='text-xl mt-4'>La page demandée est introuvable.</p><a href='/' class='mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded shadow hover:bg-blue-700'>Retour à l'accueil</a></div></body></html>";
        break;
}

function retournerErreur405(bool $json = false): void {
    http_response_code(405);
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    } else {
        echo "Erreur 405 : Méthode HTTP non autorisée pour cette route.";
    }
}