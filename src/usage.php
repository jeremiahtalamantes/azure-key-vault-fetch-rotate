<?php

/**
 * Fetch and rotate secrets in Azure Key Vault (with Guzzle)
 */

    /**
     * This script is called from the CRON job
     */

    // Include external libraries
    require_once 'vendor/autoload.php';

    // Load our class
    require_once 'AzureKeyVault.php';

    // Instantiate new object
    $keyVault = new AzureKeyVault();

    /**
     * Get the secret
     */
    try {
        $key = $keyVault->getKey();
        echo "Key: " . json_encode($key) . PHP_EOL;
    } catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }

    /**
     * Rotate the secret
     */
    try {
        $newKey = $keyVault->rotateKey();
        echo "New Key: " . json_encode($newKey) . PHP_EOL;
    } catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }