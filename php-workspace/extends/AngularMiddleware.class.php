<?php
class AngularMiddleware extends \Slim\Middleware
{
    public function call()
    {
        // Get reference to application
        $app = $this->app;

        // Run inner middleware and application
        $this->next->call();

        $res = $app->response();
        $body = $res->getBody();

        $app = App::getInstance();
        $res->setBody($app->twig->render('angular.html.twig'));
    }
}