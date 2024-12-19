<?php
/**
 * INTERNALERRORTest
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Opportify Insights API
 *
 * ## Overview  The **Opportify Insights API** provides access to robust and highly up to date platform. With powerful data warehouse and AI capabilities, this API is designed to help your business to make data-driven decissions and assess potential risks effectively.  ### Base URL Use the following base URL for all API requests:  ```plaintext https://api.opportify.ai/insights/v1/<endpoint>/<service> ```  ### Features - [**Email Insights:**](/docs/api-reference/email-insights)   - Validate email syntax.   - Identify email types (free, disposable, corporate or unknown).   - Real time verification if the emailDNS:     - Is recheable: Confirms if the email domain has valid MX DNS records using DNS lookup.     - Is deliverable: Checks if the email address exists with its provider and is deliverable using SMTP handshake simulation.     - Is catch-all: Determines if the email domain is configured as a catch-all, which accepts emails for all addresses within the domain.   - Correct well-known misspelled email addresses on the fly.   - Risk assessment based on AI-driven normalized score (200-1000) and static thresholds.      [Access Documentation >>](/docs/api-reference/email-insights)  - [**IP Analysis:**](/docs/api-reference/ip-insights)   - Identify the connection type: `wired`, `mobile`, `enterprise`, `satellite`, `VPN`, `cloud-provider`, `open-proxy`, or `tor`. See the `connectionType` element for details.   - Identify geolocation information (country, city, timezone, language, and more).   - Obtain main WHOIS details such as RIR, ASN, organization, and contacts (abuse, admin, and tech).   - Retrieve information if identified as part of a known trusted provider. (e.g. ZTNE (Zero Trust Network Access))   - Retrieve blocklist up to date statuses, active reports, and latest detection.   - Assess IP risk levels based on an AI-driven normalized score (200-1000) and static thresholds.    [Access Documentation >>](/docs/api-reference/ip-insights)
 *
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.10.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Please update the test case below to test the model.
 */

namespace OpenAPI\Client\Test\Model;

use PHPUnit\Framework\TestCase;

/**
 * INTERNALERRORTest Class Doc Comment
 *
 * @category    Class
 * @description INTERNALERROR
 * @package     OpenAPI\Client
 * @author      OpenAPI Generator team
 * @link        https://openapi-generator.tech
 */
class INTERNALERRORTest extends TestCase
{

    /**
     * Setup before running any test case
     */
    public static function setUpBeforeClass(): void
    {
    }

    /**
     * Setup before running each test case
     */
    public function setUp(): void
    {
    }

    /**
     * Clean up after running each test case
     */
    public function tearDown(): void
    {
    }

    /**
     * Clean up after running all test cases
     */
    public static function tearDownAfterClass(): void
    {
    }

    /**
     * Test "INTERNALERROR"
     */
    public function testINTERNALERROR()
    {
        // TODO: implement
        self::markTestIncomplete('Not implemented');
    }

    /**
     * Test attribute "message"
     */
    public function testPropertyMessage()
    {
        // TODO: implement
        self::markTestIncomplete('Not implemented');
    }

    /**
     * Test attribute "code"
     */
    public function testPropertyCode()
    {
        // TODO: implement
        self::markTestIncomplete('Not implemented');
    }
}
