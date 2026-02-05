<?php

use App\Core\Router;

return function (Router $router): void {
    $router->get("/", "DashboardController@index");
    $router->get("/login", "AuthController@loginForm");
    $router->post("/login", "AuthController@login");
    $router->get("/projects", "ProjectController@index");
    $router->get("/projects/new", "ProjectController@create");
    $router->post("/projects", "ProjectController@store");
    $router->get("/projects/show", "ProjectController@show");
    $router->get("/projects/edit", "ProjectController@edit");
    $router->post("/projects/update", "ProjectController@update");
    $router->post("/projects/delete", "ProjectController@delete");
    $router->post("/projects/archive", "ProjectController@archive");
    $router->get("/projects/archived", "ProjectController@archived");
    $router->post("/projects/restore", "ProjectController@restore");
    $router->get("/imports/new", "ImportController@create");
    $router->post("/imports", "ImportController@store");
    $router->get("/imports/edit", "ImportController@edit");
    $router->post("/imports/update", "ImportController@update");
    $router->post("/imports/delete", "ImportController@delete");
    $router->post("/imports/archive", "ImportController@archive");
    $router->post("/imports/restore", "ImportController@restore");
    $router->get("/templates/whatsapp", "TemplateController@index");
    $router->post("/templates/whatsapp", "TemplateController@store");
    $router->post("/templates/whatsapp/send", "TemplateController@send");
    $router->get("/leads", "LeadController@index");
    $router->get("/leads/search", "MapsController@form");
    $router->post("/leads/search", "MapsController@search");
    $router->post("/leads/save-google", "MapsController@save");
    $router->get("/leads/new", "LeadController@create");
    $router->post("/leads", "LeadController@store");
    $router->get("/leads/show", "LeadController@show");
    $router->get("/leads/edit", "LeadController@edit");
    $router->post("/leads/update", "LeadController@update");
    $router->post("/leads/status", "LeadController@updateStatus");
    $router->post("/leads/notes", "LeadController@updateNotes");
    $router->post("/leads/delete", "LeadController@delete");
    $router->post("/leads/interaction", "LeadController@addInteraction");
    $router->post("/leads/whatsapp", "LeadController@sendWhatsapp");
    $router->get("/leads/export", "LeadController@export");
    $router->get("/services", "ServiceController@index");
    $router->get("/proposals", "ProposalController@index");
};
