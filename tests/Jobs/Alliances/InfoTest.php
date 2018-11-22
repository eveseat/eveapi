<?php

namespace Seat\Eveapi\Tests\Jobs\Alliances;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Tests\Init\AbstractBaseTest;
use Seat\Eveapi\Jobs\Alliances\Info;

class InfoTest extends AbstractBaseTest
{

    /**
     * @test
     * @throws \Throwable
     */
    public function testHandle()
    {
        $asset = __DIR__ . "/../../Assets/alliance.json";

        $alliance = new Alliance([
            'alliance_id' => 1,
        ]);

        $info = new Info();
        $info->setAlliance($alliance);
        $info->handle();

        $alliance = Alliance::find(1);

        // reset timestamp fields in order to avoid invalid return
        $alliance->setCreatedAt(null);
        $alliance->setUpdatedAt(null);

        $this->assertJsonStringEqualsJsonFile($asset, $alliance->toJson());
    }
}
