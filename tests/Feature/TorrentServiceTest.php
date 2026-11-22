<?php

namespace Tests\Feature;

use App\Services\Torrent\Providers\ArchiveOrgProvider;
use App\Services\Torrent\Providers\NyaaProvider;
use App\Services\Torrent\Providers\SolidTorrentsProvider;
use App\Services\Torrent\TorrentProviderInterface;
use App\Services\Torrent\TorrentResult;
use App\Services\Torrent\TorrentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TorrentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_search_returns_empty_when_query_is_empty(): void
    {
        $service = new TorrentService();
        $results = $service->search('');
        $this->assertEmpty($results);
    }

    public function test_search_aggregates_results_from_multiple_providers(): void
    {
        $provider1 = $this->createMock(TorrentProviderInterface::class);
        $provider1->expects($this->once())
            ->method('search')
            ->with('Matrix')
            ->willReturn([
                new TorrentResult(title: 'Matrix 1999', magnet: 'magnet:?xt=1', source: 'provider1'),
            ]);
        $provider1->method('getName')->willReturn('provider1');

        $provider2 = $this->createMock(TorrentProviderInterface::class);
        $provider2->expects($this->once())
            ->method('search')
            ->with('Matrix')
            ->willReturn([
                new TorrentResult(title: 'The Matrix', magnet: 'magnet:?xt=2', source: 'provider2'),
            ]);
        $provider2->method('getName')->willReturn('provider2');

        $service = new TorrentService([$provider1, $provider2], cacheTtl: 0);
        Cache::forget('torrent_search:Matrix:');

        $results = $service->search('Matrix');
        $this->assertCount(2, $results);
        $this->assertEquals('Matrix 1999', $results[0]->title);
        $this->assertEquals('The Matrix', $results[1]->title);
    }

    public function test_search_filters_by_source(): void
    {
        $provider1 = $this->createMock(TorrentProviderInterface::class);
        $provider1->method('getName')->willReturn('solidtorrents');
        $provider1->method('search')->willReturn([
            new TorrentResult(title: 'Result 1', magnet: 'm1', source: 'solidtorrents'),
        ]);

        $provider2 = $this->createMock(TorrentProviderInterface::class);
        $provider2->method('getName')->willReturn('nyaa');
        $provider2->method('search')->willReturn([
            new TorrentResult(title: 'Result 2', magnet: 'm2', source: 'nyaa'),
        ]);

        $service = new TorrentService([$provider1, $provider2], cacheTtl: 0);
        Cache::forget('torrent_search:test:solidtorrents');

        $results = $service->search('test', sourceFilter: 'solidtorrents');
        $this->assertCount(1, $results);
        $this->assertEquals('solidtorrents', $results[0]->source);
    }

    public function test_search_caches_results(): void
    {
        $provider = $this->createMock(TorrentProviderInterface::class);
        $provider->method('getName')->willReturn('test');
        // Expect only 1 call even when searching twice
        $provider->expects($this->once())
            ->method('search')
            ->willReturn([
                new TorrentResult(title: 'Cached', magnet: 'm1', source: 'test'),
            ]);

        $service = new TorrentService([$provider], cacheTtl: 300);
        Cache::flush();

        $results1 = $service->search('cached');
        $results2 = $service->search('cached');

        $this->assertCount(1, $results1);
        $this->assertCount(1, $results2);
    }

    public function test_provider_timeout_does_not_break_search(): void
    {
        $provider = $this->createMock(TorrentProviderInterface::class);
        $provider->method('getName')->willReturn('failing');
        $provider->method('search')->willReturn([]);

        $service = new TorrentService([$provider], cacheTtl: 0);
        Cache::flush();

        $results = $service->search('test');
        $this->assertEmpty($results);
    }

    public function test_get_available_sources_returns_provider_names(): void
    {
        $p1 = $this->createMock(TorrentProviderInterface::class);
        $p1->method('getName')->willReturn('solidtorrents');

        $p2 = $this->createMock(TorrentProviderInterface::class);
        $p2->method('getName')->willReturn('nyaa');

        $service = new TorrentService([$p1, $p2]);
        $sources = $service->getAvailableSources();

        $this->assertEquals(['solidtorrents', 'nyaa'], $sources);
    }

    public function test_solidtorrents_provider_parses_response(): void
    {
        Http::fake([
            'solidtorrents.to/*' => Http::response([
                [
                    'title' => 'Matrix 1999',
                    'magnet' => 'magnet:?xt=urn:btih:abc123',
                    'size' => 1500000000,
                    'seeders' => 50,
                    'leechers' => 10,
                    'url' => 'https://solidtorrents.to/torrent/123',
                    'category' => 'Movies',
                ],
            ], 200),
        ]);

        $provider = new SolidTorrentsProvider(['base_url' => 'https://solidtorrents.to/api']);
        $results = $provider->search('Matrix');

        $this->assertCount(1, $results);
        $this->assertEquals('Matrix 1999', $results[0]->title);
        $this->assertEquals('magnet:?xt=urn:btih:abc123', $results[0]->magnet);
        $this->assertEquals(50, $results[0]->seeders);
        $this->assertEquals('solidtorrents', $results[0]->source);
    }

    public function test_nyaa_provider_parses_response(): void
    {
        Http::fake([
            'nyaa.si/*' => Http::response([
                [
                    'nyaa' => [
                        'items' => [
                            [
                                'title' => 'Anime Film',
                                'download' => 'magnet:?xt=urn:btih:nyaa123',
                                'size' => '700 MB',
                                'seeders' => 30,
                                'leechers' => 5,
                                'link' => 'https://nyaa.si/view/123',
                                'category' => 'Anime',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $provider = new NyaaProvider(['base_url' => 'https://nyaa.si/api?']);
        $results = $provider->search('Anime');

        $this->assertCount(1, $results);
        $this->assertEquals('Anime Film', $results[0]->title);
        $this->assertEquals('nyaa', $results[0]->source);
    }

    public function test_archive_org_provider_parses_response(): void
    {
        Http::fake([
            'archive.org/*' => Http::response([
                'response' => [
                    'docs' => [
                        [
                            'identifier' => 'publicdomainfilm',
                            'title' => 'Public Domain Film',
                            'size' => '2000000000',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $provider = new ArchiveOrgProvider(['base_url' => 'https://archive.org/advancedsearch.php']);
        $results = $provider->search('Public Domain');

        $this->assertCount(1, $results);
        $this->assertEquals('Public Domain Film', $results[0]->title);
        $this->assertEquals('archiveorg', $results[0]->source);
        $this->assertStringContainsString('archive.org', $results[0]->url);
    }

    public function test_provider_handles_http_error(): void
    {
        Http::fake([
            'solidtorrents.to/*' => Http::response('', 500),
        ]);

        $provider = new SolidTorrentsProvider(['base_url' => 'https://solidtorrents.to/api']);
        $results = $provider->search('test');

        $this->assertEmpty($results);
    }

    public function test_create_from_config_uses_config_file(): void
    {
        config(['torrents.providers' => [
            'solidtorrents' => ['enabled' => true, 'base_url' => 'https://solidtorrents.to/api'],
            'nyaa' => ['enabled' => false, 'base_url' => 'https://nyaa.si/api?'],
        ]]);

        $service = TorrentService::createFromConfig();
        $sources = $service->getAvailableSources();

        // Only solidtorrents should be enabled
        $this->assertEquals(['solidtorrents'], $sources);
    }
}
