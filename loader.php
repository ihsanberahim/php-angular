<?php
require_once ROOT.'/vendor/autoload.php';

require_once WORKSPACE.'/helpers.php';
require_once WORKSPACE.'/app.php';

$app = App::getInstance();

$finder = new Symfony\Component\Finder\Finder();

/*------------------------------------------------------------------------
SECTION : TWIG
------------------------------------------------------------------------*/
$twig = new Twig_Environment(new Twig_Loader_Filesystem(array(TEMPLATES)));

//Patch to fix angular conflict
$twig->setLexer(new Twig_Lexer(new Twig_Environment(), array(
    'tag_variable'  => array('[[', ']]')
)));

$app->twig = $twig;

$app->twig->addGlobal('HOME', $app->slim->request->getRootUri() );
$app->twig->addGlobal('GIT_COMMIT_ID', get_last_commit('commit') );
/*----------------------------------------------------------------------*/

/*------------------------------------------------------------------------
SECTION : LOAD CONTROLLERS
------------------------------------------------------------------------*/
$ctrl_dir = realpath(WORKSPACE.'/controllers');
$iterator = $finder->files()
  ->name('*.class.php')
  ->in($ctrl_dir);
foreach ($iterator as $file)
{
	require_once $file->getRealpath();
}
/*----------------------------------------------------------------------*/

/*------------------------------------------------------------------------
SECTION : LOAD EXTENDS
------------------------------------------------------------------------*/
$extends_dir = realpath(WORKSPACE.'/extends');
$iterator = $finder->files()
  ->name('*.class.php')
  ->in($extends_dir);
foreach ($iterator as $file)
{
	require_once $file->getRealpath();
}
/*----------------------------------------------------------------------*/

$app->slim->add(new AngularMiddleware());

require_once WORKSPACE.'/routes.php';