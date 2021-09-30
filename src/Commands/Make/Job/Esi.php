<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Commands\Make\Job;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeJobEsi.
 *
 * @package Seat\Eveapi\Commands\Make\Job
 */
class Esi extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $signature = 'make:job:esi {name : The generated class name} {endpoint : The ESI target endpoint} {--esi-version= : The ESI target endpoint version} {--scope= : Require scope to access to the ESI endpoint} {--paginated : Determine if target endpoint is paginated}';

    /**
     * @var string
     */
    protected $description = 'Create a new ESI job class';

    /**
     * @var string
     */
    protected $type = 'Job';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('paginated') ?
            __DIR__ . '/../stubs/esi-paginated-job.stub' : __DIR__ . '/../stubs/esi-job.stub';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $this->replaceEndpoint($stub, $this->argument('endpoint'));

        if (! is_null($this->option('esi-version')))
            $this->replaceVersion($stub, $this->option('esi-version'));

        if (! is_null($this->option('scope')))
            $this->replaceScope($stub, $this->option('scope'));

        return $stub;
    }

    /**
     * Replace the endpoint for the given stub.
     *
     * @param  string  $stub
     * @param  string  $endpoint
     * @return $this
     */
    protected function replaceEndpoint(string &$stub, string $endpoint)
    {
        if (strlen($endpoint) > 0)
            $stub = str_replace('/dummy/endpoint/', $endpoint, $stub);

        return $this;
    }

    /**
     * Replace the endpoint version for the given stub.
     *
     * @param  string  $stub
     * @param  string  $version
     * @return $this
     */
    protected function replaceVersion(string &$stub, string $version)
    {
        if (strlen($version) > 0)
            $stub = str_replace('v1', $version, $stub);

        return $this;
    }

    /**
     * Replace the scope for the given stub.
     *
     * @param  string  $stub
     * @param  string  $scope
     * @return $this
     */
    protected function replaceScope(string &$stub, string $scope)
    {
        if (strlen($scope) > 0)
            $stub = str_replace("'public'", "'$scope'", $stub);

        return $this;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Jobs';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'esi-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify version from the endpoint which should be used by the generated job.',
            ],
            [
                'scope',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify the scope which is required by the endpoint.',
            ],
            [
                'paginated',
                null,
                InputOption::VALUE_NONE,
                'Determine if the job is applying to a paginated endpoint or not.',
            ],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of the class',
            ],
            [
                'endpoint',
                InputArgument::REQUIRED,
                'Specify the route to use by the generated job.',
            ],
        ];
    }
}
