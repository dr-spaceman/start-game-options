<?php

require_once dirname(__FILE__) . '/../config/bootstrap_app.php';

$page = $template->load('test.html');
echo $page->render(['the' => 'variables', 'go' => 'here']);
