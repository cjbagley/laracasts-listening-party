<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\ListeningParty;
use App\Models\Podcast;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcastUrl implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $url,
        private ListeningParty $listeningParty,
        private Episode $episode
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /*
         * NOTE: Would normally add a whole lot of validation
         * and error checking with the below, but for now I'm
         * just following along with the lesson and this is not
         * a production app so YAGNI
         */

        $xml = simplexml_load_file($this->url);

        $latest_episode = $xml->channel->item[0];

        $data = [
            'podcast_title' => $xml->channel->title,
            'podcast_artwork_url' => $xml->channel->image->url,
            'episode_title' => $latest_episode->title,
            'episode_media_url' => (string) $latest_episode->enclosure['url'],
        ];

        $namespaces = $xml->getNamespaces(recursive: true);
        $itunes_namespace = $namespaces['itunes'];

        $ep_length = $latest_episode->children($itunes_namespace)->duration;
        $interval = CarbonInterval::createFromFormat('H:i:s', $ep_length);
        $end_time = $this->listeningParty->start_time->copy()->add($interval);

        $podcast = Podcast::updateOrCreate([
            'title' => $data['podcast_title'],
            'artwork_url' => $data['podcast_artwork_url'],
            'rss_url' => $this->url,
        ]);

        $this->episode->podcasts()->associate($podcast);
        $this->episode->save();
        $this->episode->update([
            'title' => $data['episode_title'],
            'media_url' => $data['episode_media_url'],
        ]);

        $this->listeningParty->update([
            'end_time' => $end_time,
        ]);
    }
}
