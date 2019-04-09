<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/{name}', function (Request $request, Response $response, $args = []) {
    $name = $args['name'];
    $this->logger->addInfo('Hello ' . $name);
    return $this->view->render($response, 'hello.html.twig', [
        'name' => $name
    ]);
});

$app->get('/listings/new', function (Request $request, Response $response) {
    try {
        return $this->view->render($response, 'new_listing.html.twig');
    } catch (Exception $e) {
        //TODO: redirect?
        $this->logger->addError("/listings/new GET throw exception: " . $e);
    }
});

$app->post('/listings/new', function (Request $request, Response $response) {
    try {
        $this->logger->addInfo("Recieved post params:" . print_r($request->getParams(), true));

        $query = "INSERT INTO listings(email, category, subcategory, price, quantity) VALUES(?,?,?,?,?);";
        $params = $request->getParams();
        $this->db->execute($query, $params);

        $this->logger->addInfo("Parameters inserted");
        return $this->view->render($response, 'new_listing.html.twig', [
        'message' => "New listing inserted."

    ]);
    } catch (Exception $e) {
        $this->logger->addError("/listings/new POST throw exception: " . $e);
    }
});
