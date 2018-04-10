<?php

/*
This file will automatically be included before EACH run.

Use it to configure atoum or anything that needs to be done before EACH run.

More information on documentation:
[en] http://docs.atoum.org/en/chapter3.html#Configuration-files
[fr] http://docs.atoum.org/fr/chapter3.html#Fichier-de-configuration
*/

use \mageekguy\atoum,
    \mageekguy\atoum\reports;

$report = $script->addDefaultReport();

/*
LOGO
*/
// This will add the atoum logo before each run.
$report->addField(new atoum\report\fields\runner\atoum\logo());

// This will add a green or red logo after each run depending on its status.
$report->addField(new atoum\report\fields\runner\result\logo());
/**/

$script->getRunner()->addTestsFromDirectory(__DIR__ . '/test/unit/src');

if(file_exists('/home/travis'))
{
    /*
    Publish code coverage report on coveralls.io
    */
    $sources = './src';
    $token = 'SoXzLQVMf3fSHaiEANaQhclas6bsWZjHA';
    $coverallsReport = new reports\asynchronous\coveralls($sources, $token);
    
    /*
    If you are using Travis-CI (or any other CI tool), you should customize the report
    * https://coveralls.io/docs/api
    * http://about.travis-ci.org/docs/user/ci-environment/#Environment-variables
    * https://wiki.jenkins-ci.org/display/JENKINS/Building+a+software+project#Buildingasoftwareproject-JenkinsSetEnvironmentVariables
    */
    $defaultFinder = $coverallsReport->getBranchFinder();
    $coverallsReport
        ->setBranchFinder(function() use ($defaultFinder) {
            if (($branch = getenv('TRAVIS_BRANCH')) === false)
            {
                $branch = $defaultFinder();
            }
    
            return $branch;
        })
        ->setServiceName(getenv('TRAVIS') ? 'travis-ci' : null)
        ->setServiceJobId(getenv('TRAVIS_JOB_ID') ?: null)
        ->addDefaultWriter()
    ;
    
    $runner->addReport($coverallsReport);
    
    //Scrutinizer coverage
	$cloverWriter = new atoum\writers\file('clover.xml');
	$cloverReport = new atoum\reports\asynchronous\clover();
	$cloverReport->addWriter($cloverWriter);

	$runner->addReport($cloverReport);
}
