<?php

namespace Seat\Eveapi\Tests\ESI;


use GuzzleHttp\Client;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Seat\Eveapi\Jobs\EsiBase;
use Symfony\Component\Finder\Finder;

class EndpointsTest extends TestCase
{
    private $failures = [];

    public function testEndpoints()
    {
        // pull latest swagger definition from ESI
        $client = new Client();
        $request = $client->get('https://esi.evetech.net/latest/swagger.json');
        $swagger = json_decode($request->getBody(), true);

        // retrieve all ESI Jobs for checkup
        $esi_jobs = $this->getAllFcqns(__DIR__ . '/../../src/Jobs');

        foreach ($esi_jobs as $job) {

            $class = new ReflectionClass($job);

            if ($class->isAbstract())
                continue;

            if (! $class->isSubclassOf(EsiBase::class))
                continue;

            $object = new $job;

            print sprintf('Checking validity for [%s] %s@%s%s', $object->getMethod(), $object->getEndpoint(), $object->getVersion(), PHP_EOL);

            try {
                // ensure the endpoint still exists
                $this->assertTrue(
                    array_key_exists($object->getEndpoint(), $swagger['paths']),
                    sprintf('%s endpoint has been removed from ESI.', $object->getEndpoint()));

                // ensure the method still exists for this endpoint
                $this->assertTrue(
                    array_key_exists($object->getMethod(), $swagger['paths'][$object->getEndpoint()]),
                    sprintf('[%s] %s has been removed from ESI.', $object->getMethod(), $object->getEndpoint()));

                // collection versions and remove dev flag
                $swagger_endpoint_versions = array_filter($swagger['paths'][$object->getEndpoint()][$object->getMethod()]['x-alternate-versions'], function ($version) {
                    return ! in_array($version, ['dev', 'legacy']);
                });

                // sort versions from lower to bigger
                sort($swagger_endpoint_versions);

                // ensure the used version is not deprecated
                $this->assertEquals(
                    $object->getVersion(), array_last($swagger_endpoint_versions),
                    sprintf('[%s] %s@%s is deprecated: %s are available',
                        strtoupper($object->getMethod()), $object->getEndpoint(), $object->getVersion(), array_last($swagger_endpoint_versions)));

            } catch (ExpectationFailedException $e) {
                $this->failures[] = $e->getMessage();
            }

            unset($object);
        }

        if (! empty($this->failures)) {
            throw new ExpectationFailedException(
                sprintf("%d tested jobs with %d outdated jobs: \r\n\t%s", count($esi_jobs), count($this->failures), implode("\r\n\t", $this->failures))
            );
        }
    }

    /**
     * @param string $filename
     * @return string
     *
     * @see https://gnugat.github.io/2014/11/26/find-all-available-fqcn.html
     */
    private function getFullNamespace(string $filename): string
    {
        $match = [];
        $lines = file($filename);
        $namespace_lines = preg_grep('/^namespace /', $lines);
        preg_match('/^namespace (.*);\r\n$/', array_shift($namespace_lines), $match);

        return array_pop($match);
    }

    /**
     * @param string $filename
     * @return string
     *
     * @see https://gnugat.github.io/2014/11/26/find-all-available-fqcn.html
     */
    private function getClassName(string $filename): string
    {
        $paths = explode(DIRECTORY_SEPARATOR, $filename);
        $filename = array_pop($paths);
        $name_with_extension = explode('.', $filename);

        return array_shift($name_with_extension);
    }

    /**
     * @param $path
     * @return array
     *
     * @see https://gnugat.github.io/2014/11/26/find-all-available-fqcn.html
     */
    private function getFilenames($path): array
    {
        $filenames = [];
        $finder = Finder::create()->files()->in($path)->name('*.php');

        foreach ($finder as $file)
            $filenames[] = $file->getRealPath();

        return $filenames;
    }

    /**
     * @param $path
     * @return array
     *
     * @see https://gnugat.github.io/2014/11/26/find-all-available-fqcn.html
     */
    private function getAllFcqns($path): array
    {
        $fcqns = [];
        $filenames = $this->getFilenames($path);

        foreach ($filenames as $filename) {
            $fcqns[] = $this->getFullNamespace($filename) . '\\' . $this->getClassName($filename);
        }

        return $fcqns;
    }

}
