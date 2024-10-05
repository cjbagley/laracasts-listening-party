<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\ListeningParty;
use App\Models\Podcast;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessPodcastUrl implements ShouldQueue
{
    use Queueable;

    const BITRATE_KBPS = 128000;

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
        $itunes_namespace = $namespaces['itunes'] ?? null;
        $episode_length = null;

        if ($itunes_namespace) {
            $episode_length = (string) $latest_episode->children($itunes_namespace)->duration;
        }

        if ($episode_length === null || $episode_length === '' || $episode_length === '0') {
            $filesize = (int) $latest_episode->enclosure['length'];
            $duration_in_seconds = ceil($filesize * 8 / self::BITRATE_KBPS);
            $episode_length = (string) $duration_in_seconds;
        }

        /*
         * Note: Normally I would extract this to a separate function, along
         * with custom Exception, but in the interest of time and following
         * along with the lesson, as left as per the video. Again, not production
         * app and YAGNI when the focus is brushing up on Livewire Volt.
         */
        try {
            if (str_contains($episode_length, ':')) {
                $parts = explode(':', $episode_length);
                if (count($parts) === 2) {
                    $interval = CarbonInterval::createFromFormat('i:s', $episode_length);
                } elseif (count($parts) === 3) {
                    $interval = CarbonInterval::createFromFormat('H:i:s', $episode_length);
                } else {
                    throw new \Exception('Invalid episode_length');
                }
            } else {
                $interval = CarbonInterval::seconds((int) $episode_length);
            }
        } catch (\Exception $exception) {
            Log::error('Error parsing episode duration: '.$exception->getMessage());
            $interval = CarbonInterval::hour();
        }

        $end_time = $this->listeningParty->start_time->copy()->add($interval);

        $podcast = Podcast::updateOrCreate([
            'title' => $data['podcast_title'],
            'artwork_url' => $data['podcast_artwork_url'],
            'rss_url' => $this->url,
        ]);

        $this->episode->podcast()->associate($podcast);
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
