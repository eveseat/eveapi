<?php

namespace Seat\Eveapi\Tests\ESI;


use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Seat\Eveapi\Jobs\EsiBase;
use Symfony\Component\Finder\Finder;

class EndpointsTest extends TestCase
{

    public function testEndpoints()
    {
        $client = new Client();
        $request = $client->get('https://esi.evetech.net/latest/swagger.json');
        $swagger = json_decode($request->getBody(), true);

        $esi_jobs = $this->getAllFcqns(__DIR__ . '/../../src/Jobs');

        foreach ($esi_jobs as $job) {
            // init a new object
            $object = new $job;

            // ensure the object is inheriting EsiBase
            if (!is_subclass_of($object, EsiBase::class))
                continue;

            print sprintf('Checking validity for [%s] %s@%s%s', $object->getMethod(), $object->getEndpoint(), $object->getVersion(), PHP_EOL);

            // ensure the endpoint still exists
            $this->assertTrue(
                array_key_exists($object->getEndpoint(), $swagger['paths']),
                sprintf('%s endpoint has been removed from ESI.', $object->getEndpoint()));

            // ensure the method still exists for this endpoint
            $this->assertTrue(
                array_key_exists($object->getMethod(), $swagger['paths'][$object->getEndpoint()]),
                sprintf('[%s] %s has been removed from ESI.', $object->getMethod(), $object->getEndpoint()));

            // ensure the used version is not deprecated
            $swagger_endpoint_versions = $swagger['paths'][$object->getEndpoint()][$object->getMethod()]['x-alternate-versions'];
            $this->assertContains(
                $object->getVersion(), $swagger_endpoint_versions,
                sprintf('[%s] %s@%s is deprecated: %s are available',
                    $object->getMethod(), $object->getEndpoint(), $object->getVersion(), implode(', ', $swagger_endpoint_versions)));

            unset($object);
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
