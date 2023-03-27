<?php

/**
 * Fetch and rotate secrets in Azure Key Vault (with Guzzle)
 */

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Dotenv\Dotenv;

class AzureKeyVault
{
    /**
     * Properties
     */
    private $httpClient;
    private $tenantId;
    private $clientId;
    private $clientSecret;
    private $keyVaultName;
    private $keyName;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize Guzzle HTTP client
        $this->httpClient = new Client();

        // Load environment variables from the .env file
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // Set required properties from environment variables
        $this->tenantId = getenv('AZURE_TENANT_ID');
        $this->clientId = getenv('AZURE_CLIENT_ID');
        $this->clientSecret = getenv('AZURE_CLIENT_SECRET');
        $this->keyVaultName = getenv('AZURE_KEY_VAULT_NAME');
        $this->keyName = getenv('KEY_NAME');
    }

    /**
     * Member methods 
     */


    /**
     * Get an access token to interact with the Azure REST API with Guzzle
     */
    private function getAccessToken()
    {
        try {
            $response = $this->httpClient->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/token", [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'resource' => 'https://vault.azure.net',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'];
        } catch (GuzzleException $e) {
            throw new Exception('Error fetching access token: ' . $e->getMessage());
        }
    }

    /**
     * Fetch the key from Azure Key Vault
     */
    public function getKey()
    {
        try {
            $accessToken = $this->getAccessToken();
            $response = $this->httpClient->get("https://{$this->keyVaultName}.vault.azure.net/keys/{$this->keyName}", [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                ],
                'query' => [
                    'api-version' => '7.0',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data;
        } catch (GuzzleException $e) {
            throw new Exception('Error fetching key: ' . $e->getMessage());
        }
    }

    /**
     * Rotate the key with a new random string
     */
    public function rotateKey()
    {
        try {
            $accessToken = $this->getAccessToken();
            $newKeyValue = bin2hex(random_bytes(32)); // Generate a 64 character random string

            $response = $this->httpClient->put("https://{$this->keyVaultName}.vault.azure.net/keys/{$this->keyName}", [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'api-version' => '7.0',
                ],
                'body' => json_encode([
                    'k' => $newKeyValue,
                ]),
            ]);

            $data = json_decode($response->getBody(), true);
            return $data;
        } catch (GuzzleException $e) {
            throw new Exception('Error rotating key: ' . $e->getMessage());
        }
    }
}