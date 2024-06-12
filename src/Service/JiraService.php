<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JiraService
{
    private $client;
    private $apiUrl;
    private $email;
    private $apiToken;

    public function __construct(string $apiUrl, string $email, string $apiToken)
    {
        $this->client = HttpClient::create();
        $this->apiUrl = $apiUrl;
        $this->email = $email;
        $this->apiToken = $apiToken;
    }

    public function createTicket($summary, $priority, $reporterEmail, $collection, $link)
    {
        $customFieldCollectionId = 'customfield_10064'; // Replace with your actual custom field ID for Collection
        $customFieldLinkId = 'customfield_10063'; // Replace with your actual custom field ID for Link

        // Fetch the user by email to get the accountId
        $reporterAccountId = $this->getUserAccountIdByEmail($reporterEmail);

        $data = [
            'fields' => [
                'project' => [
                    'key' => 'CM',
                ],
                'summary' => $summary,
                'priority' => [
                    'name' => $priority,
                ],
                'reporter' => [
                    'id' => $reporterAccountId,
                ],
                $customFieldCollectionId => $collection,
                $customFieldLinkId => $link,
                'issuetype' => [
                    'name' => 'Task',
                ],
            ],
        ];

        try {
            $response = $this->client->request('POST', $this->apiUrl . '/issue', [
                'json' => $data,
                'auth_basic' => [$this->email, $this->apiToken],
            ]);

            return $response->toArray();
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            $responseContent = $e->getResponse()->getContent(false);
            throw new \Exception('Jira API error: ' . $responseContent);
        }
    }
    private function getUserAccountIdByEmail($email)
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl . '/user/search?query=' . $email, [
                'auth_basic' => [$this->email, $this->apiToken],
            ]);
    
            $users = $response->toArray();
    
            if (count($users) > 0) {
                return $users[0]['accountId'];
            } else {
                // Optionally create the user if not found
                $newUserResponse = $this->client->request('POST', $this->apiUrl . '/user', [
                    'json' => [
                        'emailAddress' => $email,
                        'displayName' => 'New User',
                        'notification' => 'true',
                        'products' => ['jira-servicedesk'], // Use 'jira-servicedesk' as the product key
                        'groups' => ['users'] // Ensure the user is added to the correct group
                    ],
                    'auth_basic' => [$this->email, $this->apiToken],
                ]);
    
                $newUser = $newUserResponse->toArray();
                return $newUser['accountId'];
            }
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            $responseContent = $e->getResponse()->getContent(false);
            throw new \Exception('Jira API error: ' . $responseContent);
        }
    }
    
}
