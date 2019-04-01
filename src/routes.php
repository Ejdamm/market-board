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
    return $this->view->render($response, 'new_listing.html.twig');
});

$app->post('/listings/new', function (Request $request, Response $response) {
    $query = "INSERT INTO listings(email, category, subcategory, price, quantity) VALUES(?,?,?,?,?);";
    $params = $request->getParams();
    $this->db->execute($query, $params);
    return $this->view->render($response, 'new_listing.html.twig', [
        'message' => "New listing inserted."
    ]);
});
