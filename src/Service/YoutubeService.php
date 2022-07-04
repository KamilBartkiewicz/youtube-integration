<?php

namespace App\Service;

use Google\Client;
use Google\Service\YouTube\SubscriptionListResponse;
use Google_Service_YouTube;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class YoutubeService
{
    protected const ACCESS_TOKEN = 'access_token';

    private Client $Client;
    private Session $Session;
    private LoggerInterface $Logger;

    public function __construct(RequestStack $RequestStack, LoggerInterface $Logger)
    {
        $this->Client = new Client();
        $this->Logger = $Logger;
        try {
            $this->Client->setAuthConfig('../config/client_secret.json');
            $this->Client->addScope(GOOGLE_SERVICE_YOUTUBE::YOUTUBE);
            $this->Client->setRedirectUri('http://localhost/');
            $this->Session = $RequestStack->getSession();
        } catch (\Exception $e) {
            $this->Logger->error($e->getMessage());
        }
    }


    /**
     * @return array
     */
    public function getChannels(): array
    {
        $this->Client->setAccessToken($this->Session->get(self::ACCESS_TOKEN));
        $Youtube = new Google_Service_YouTube($this->Client);
        $Subscriptions = $Youtube->subscriptions->listSubscriptions(
            'snippet,contentDetails',
            ['mine' => 'mine', 'maxResults' => 50]
        );
        if (!empty($Subscriptions->getItems())) {
            return $this->prepareSubscribedChannelsData(
                $Youtube->channels->listChannels(
                    'snippet,contentDetails,statistics',
                    ['id' => $this->getChannelsIdAsStings($Subscriptions)]
                )->getItems()
            );
        }
        return [];
    }

    /**
     * @return string
     */
    public function authorizeGoogleAccount(): string
    {
        return $this->Client->createAuthUrl();
    }

    /**
     * @return mixed
     */
    public function checkSessionData(): mixed
    {
        return $this->Session->get(self::ACCESS_TOKEN, false);
    }

    /**
     * @param string $token
     * @return void
     */
    public function setGoogleAuthData(string $token): void
    {
        $this->Client->fetchAccessTokenWithAuthCode($token);
        $this->Session->set(self::ACCESS_TOKEN, $this->Client->getAccessToken());
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareSubscribedChannelsData(array $data): array
    {
        $channels = [];
        foreach ($data as $channel) {
            $channels[$channel->getSnippet()->getTitle()] = [
                'publishedAt' => $channel
                    ->getSnippet()
                    ->getPublishedAt(),
                'img' => $channel
                    ->getSnippet()
                    ->getThumbnails()
                    ->getMedium()
                    ->getUrl(),
                'subscribers' => $channel
                    ->getStatistics()
                    ->getSubscriberCount(),
                'views' => $channel
                    ->getStatistics()
                    ->getViewCount(),
                'videos' => $channel
                    ->getStatistics()
                    ->getVideoCount()
            ];
        }
        return $channels;
    }

    /**
     * @param SubscriptionListResponse $Subscriptions
     * @return string
     */
    private function getChannelsIdAsStings(SubscriptionListResponse $Subscriptions): string
    {
        $ids = [];
        foreach ($Subscriptions->getItems() as $Subscription) {
            $ids[] = $Subscription->getSnippet()
                ->getResourceId()
                ->getChannelId();
        }
        return implode(',', $ids);
    }
}
