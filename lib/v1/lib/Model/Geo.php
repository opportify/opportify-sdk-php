<?php

/**
 * Geo
 *
 * PHP version 7.4
 *
 * @category Class
 *
 * @author   OpenAPI Generator team
 *
 * @link     https://openapi-generator.tech
 */

/**
 * Opportify Insights API
 *
 * ## Overview  The **Opportify Insights API** provides access to a powerful and up-to-date platform. With advanced data warehousing and AI-driven capabilities, this API is designed to empower your business to make informed, data-driven decisions and effectively assess potential risks.  ### Base URL Use the following base URL for all API requests:  ```plaintext https://api.opportify.ai/insights/v1/<service>/<endpoint> ```  ### Features - [**Email Insights:**](/docs/api/api-reference/email-insights)   - Validate email syntax.   - Identify email types (free, disposable, corporate or unknown).   - Real time verifications:     - Reachable: Confirms if the email domain has valid MX DNS records using DNS lookup.     - Deliverable: Simulates an SMTP handshake to check if the email address exists and is deliverable.     - Catch-All: Detects if the domain accepts all emails (catch-all configuration).   - Intelligent Error Correction: Automatically corrects well-known misspelled email addresses.   - Risk Report: Provides an AI-driven normalized score (200-1000) to evaluate email risk, using predefined thresholds.      [Access Documentation >>](/docs/api/api-reference/email-insights)  - [**IP Insights:**](/docs/api/api-reference/ip-insights)   - Connection types: Detects connection types such as `wired`, `mobile`, `enterprise`, `satellite`, `VPN`, `cloud-provider`, `open-proxy`, or `Tor`.   - Geo location: Delivers detailed insights such as country, city, timezone, language preferences, and additional location-based information to enhance regional understanding.   - WHOIS: Provides main details including RIR, ASN, organization, and abuse/admin/technical contacts.   - Trusted Provider Recognition: Identifies if the IP is part of a known trusted provider (e.g., ZTNA - Zero Trust Network Access).   - Blocklist Reports: Retrieves up-to-date blocklist statuses, active reports, and the latest detections.   - Risk Report: Delivers an AI-driven normalized score (200-1000) to evaluate IP risk, supported by predefined thresholds.    [Access Documentation >>](/docs/api/api-reference/ip-insights)  ### Authentication & Security - **API Key:** Access to the API requires an API key, which must be included in the request headers. Businesses can generate unlimited API keys directly from their account, offering flexibility and ease of use.  - **ACL Rules:** Enhance security with Access Control Lists (ACL), allowing you to restrict API access from specific IP addresses or ranges. This feature provides an additional layer of protection by ensuring only authorized IPs can interact with the API. - **No Query Parameters:** As a precautionary measure, our API avoids the use of query parameters for all operations, including authentication and handling Personally Identifiable Information (PII). This approach minimizes security risks by preventing sensitive data from being exposed in access logs, browser history, cached URLs, debugging tools, or inadvertently shared URLs. All sensitive information is securely transmitted through headers or the request body.
 *
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.10.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace OpenAPI\Client\Model;

use ArrayAccess;
use OpenAPI\Client\ObjectSerializer;

/**
 * Geo Class Doc Comment
 *
 * @category Class
 *
 * @description ### Geolocation Determination &amp; Confidence Levels Geolocation details are derived by analyzing the provided IP address using data aggregated from a wide range of sources, both official and unofficial (such as user-generated data, open-source, or crowdsourced). This data is meticulously evaluated and ranked using a proprietary weighted reliability score that is tailored to the specific characteristics and trustworthiness of each data source.  ---  #### Confidence Levels  The geolocation process assigns a confidence level to each level of granularity. These levels reflect the probability of accuracy based on the reliability of the data and analysis:  - **Continent-Level (99%)**: The determination of the continent is highly reliable, with a near-certain accuracy rate of 99%. - **Country-Level (98%)**: Locating the specific country has a very high accuracy of 98%, reflecting reliable cross-verification. - **Region-Level (70–90%)**: Identifying regions (such as states or provinces) has moderate to high accuracy, depending on the data quality and density for the given area. - **City-Level (50–70%)**: Pinpointing the specific city is moderately accurate, influenced by factors such as ISP data resolution and urban vs. rural settings. - **Specific Area/Point (5–40%)**: Pinpointing a highly specific area (e.g., a neighborhood or street) has a significantly lower confidence level due to inherent limitations in IP-based geolocation technology.  ---  #### Key Features  - **Alphabetical Object Sorting**:     The keys in the returned geolocation object are consistently sorted alphabetically, ensuring a predictable structure for easier integration and parsing.  ---  ### Response Elements
 *
 * @author   OpenAPI Generator team
 *
 * @link     https://openapi-generator.tech
 *
 * @implements \ArrayAccess<string, mixed>
 */
class Geo implements \JsonSerializable, ArrayAccess, ModelInterface
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'Geo';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'continent' => 'string',
        'country_code' => 'string',
        'country_name' => 'string',
        'country_short_name' => 'string',
        'city' => 'string',
        'currency_code' => 'string',
        'domain_extension' => 'string',
        'languages' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
        'postal_code' => 'string',
        'phone_int_code' => 'string',
        'region' => 'string',
        'timezone' => 'string',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     *
     * @phpstan-var array<string, string|null>
     *
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'continent' => null,
        'country_code' => null,
        'country_name' => null,
        'country_short_name' => null,
        'city' => null,
        'currency_code' => null,
        'domain_extension' => null,
        'languages' => null,
        'latitude' => null,
        'longitude' => null,
        'postal_code' => null,
        'phone_int_code' => null,
        'region' => null,
        'timezone' => null,
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var bool[]
     */
    protected static array $openAPINullables = [
        'continent' => false,
        'country_code' => false,
        'country_name' => false,
        'country_short_name' => false,
        'city' => false,
        'currency_code' => false,
        'domain_extension' => false,
        'languages' => false,
        'latitude' => false,
        'longitude' => false,
        'postal_code' => false,
        'phone_int_code' => false,
        'region' => false,
        'timezone' => false,
    ];

    /**
     * If a nullable field gets set to null, insert it here
     *
     * @var bool[]
     */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return bool[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param  bool[]  $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'continent' => 'continent',
        'country_code' => 'countryCode',
        'country_name' => 'countryName',
        'country_short_name' => 'countryShortName',
        'city' => 'city',
        'currency_code' => 'currencyCode',
        'domain_extension' => 'domainExtension',
        'languages' => 'languages',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'postal_code' => 'postalCode',
        'phone_int_code' => 'phoneIntCode',
        'region' => 'region',
        'timezone' => 'timezone',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'continent' => 'setContinent',
        'country_code' => 'setCountryCode',
        'country_name' => 'setCountryName',
        'country_short_name' => 'setCountryShortName',
        'city' => 'setCity',
        'currency_code' => 'setCurrencyCode',
        'domain_extension' => 'setDomainExtension',
        'languages' => 'setLanguages',
        'latitude' => 'setLatitude',
        'longitude' => 'setLongitude',
        'postal_code' => 'setPostalCode',
        'phone_int_code' => 'setPhoneIntCode',
        'region' => 'setRegion',
        'timezone' => 'setTimezone',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'continent' => 'getContinent',
        'country_code' => 'getCountryCode',
        'country_name' => 'getCountryName',
        'country_short_name' => 'getCountryShortName',
        'city' => 'getCity',
        'currency_code' => 'getCurrencyCode',
        'domain_extension' => 'getDomainExtension',
        'languages' => 'getLanguages',
        'latitude' => 'getLatitude',
        'longitude' => 'getLongitude',
        'postal_code' => 'getPostalCode',
        'phone_int_code' => 'getPhoneIntCode',
        'region' => 'getRegion',
        'timezone' => 'getTimezone',
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param  mixed[]  $data  Associated array of property values
     *                         initializing the model
     */
    public function __construct(?array $data = null)
    {
        $this->setIfExists('continent', $data ?? [], null);
        $this->setIfExists('country_code', $data ?? [], null);
        $this->setIfExists('country_name', $data ?? [], null);
        $this->setIfExists('country_short_name', $data ?? [], null);
        $this->setIfExists('city', $data ?? [], null);
        $this->setIfExists('currency_code', $data ?? [], null);
        $this->setIfExists('domain_extension', $data ?? [], null);
        $this->setIfExists('languages', $data ?? [], null);
        $this->setIfExists('latitude', $data ?? [], null);
        $this->setIfExists('longitude', $data ?? [], null);
        $this->setIfExists('postal_code', $data ?? [], null);
        $this->setIfExists('phone_int_code', $data ?? [], null);
        $this->setIfExists('region', $data ?? [], null);
        $this->setIfExists('timezone', $data ?? [], null);
    }

    /**
     * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
     * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
     * $this->openAPINullablesSetToNull array
     *
     * @param  mixed  $defaultValue
     */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }

    /**
     * Gets continent
     *
     * @return string|null
     */
    public function getContinent()
    {
        return $this->container['continent'];
    }

    /**
     * Sets continent
     *
     * @param  string|null  $continent  Name of the continent. Normalized as \"Title Case\".
     * @return self
     */
    public function setContinent($continent)
    {
        if (is_null($continent)) {
            throw new \InvalidArgumentException('non-nullable continent cannot be null');
        }
        $this->container['continent'] = $continent;

        return $this;
    }

    /**
     * Gets country_code
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->container['country_code'];
    }

    /**
     * Sets country_code
     *
     * @param  string|null  $country_code  ISO 3166-1 alpha-2 country code.
     * @return self
     */
    public function setCountryCode($country_code)
    {
        if (is_null($country_code)) {
            throw new \InvalidArgumentException('non-nullable country_code cannot be null');
        }
        $this->container['country_code'] = $country_code;

        return $this;
    }

    /**
     * Gets country_name
     *
     * @return string|null
     */
    public function getCountryName()
    {
        return $this->container['country_name'];
    }

    /**
     * Sets country_name
     *
     * @param  string|null  $country_name  Full name of the country. Normalized as \"Title Case\".
     * @return self
     */
    public function setCountryName($country_name)
    {
        if (is_null($country_name)) {
            throw new \InvalidArgumentException('non-nullable country_name cannot be null');
        }
        $this->container['country_name'] = $country_name;

        return $this;
    }

    /**
     * Gets country_short_name
     *
     * @return string|null
     */
    public function getCountryShortName()
    {
        return $this->container['country_short_name'];
    }

    /**
     * Sets country_short_name
     *
     * @param  string|null  $country_short_name  ISO 3166-1 English short version. Normalized as \"Title Case\".
     * @return self
     */
    public function setCountryShortName($country_short_name)
    {
        if (is_null($country_short_name)) {
            throw new \InvalidArgumentException('non-nullable country_short_name cannot be null');
        }
        $this->container['country_short_name'] = $country_short_name;

        return $this;
    }

    /**
     * Gets city
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->container['city'];
    }

    /**
     * Sets city
     *
     * @param  string|null  $city  Name of the city. Normalized as \"Title Case\".
     * @return self
     */
    public function setCity($city)
    {
        if (is_null($city)) {
            throw new \InvalidArgumentException('non-nullable city cannot be null');
        }
        $this->container['city'] = $city;

        return $this;
    }

    /**
     * Gets currency_code
     *
     * @return string|null
     */
    public function getCurrencyCode()
    {
        return $this->container['currency_code'];
    }

    /**
     * Sets currency_code
     *
     * @param  string|null  $currency_code  ISO 4217 currency code.
     * @return self
     */
    public function setCurrencyCode($currency_code)
    {
        if (is_null($currency_code)) {
            throw new \InvalidArgumentException('non-nullable currency_code cannot be null');
        }
        $this->container['currency_code'] = $currency_code;

        return $this;
    }

    /**
     * Gets domain_extension
     *
     * @return string|null
     */
    public function getDomainExtension()
    {
        return $this->container['domain_extension'];
    }

    /**
     * Sets domain_extension
     *
     * @param  string|null  $domain_extension  Top-level domain (TLD) for the country. 63 characters limit. IANA / ICANN defined.
     * @return self
     */
    public function setDomainExtension($domain_extension)
    {
        if (is_null($domain_extension)) {
            throw new \InvalidArgumentException('non-nullable domain_extension cannot be null');
        }
        $this->container['domain_extension'] = $domain_extension;

        return $this;
    }

    /**
     * Gets languages
     *
     * @return string|null
     */
    public function getLanguages()
    {
        return $this->container['languages'];
    }

    /**
     * Sets languages
     *
     * @param  string|null  $languages  List of languages spoken in the country separated by commas. (BCP 47 (Best Current Practice 47))
     * @return self
     */
    public function setLanguages($languages)
    {
        if (is_null($languages)) {
            throw new \InvalidArgumentException('non-nullable languages cannot be null');
        }
        $this->container['languages'] = $languages;

        return $this;
    }

    /**
     * Gets latitude
     *
     * @return float|null
     */
    public function getLatitude()
    {
        return $this->container['latitude'];
    }

    /**
     * Sets latitude
     *
     * @param  float|null  $latitude  Latitude coordinate.
     * @return self
     */
    public function setLatitude($latitude)
    {
        if (is_null($latitude)) {
            throw new \InvalidArgumentException('non-nullable latitude cannot be null');
        }
        $this->container['latitude'] = $latitude;

        return $this;
    }

    /**
     * Gets longitude
     *
     * @return float|null
     */
    public function getLongitude()
    {
        return $this->container['longitude'];
    }

    /**
     * Sets longitude
     *
     * @param  float|null  $longitude  Longitude coordinate.
     * @return self
     */
    public function setLongitude($longitude)
    {
        if (is_null($longitude)) {
            throw new \InvalidArgumentException('non-nullable longitude cannot be null');
        }
        $this->container['longitude'] = $longitude;

        return $this;
    }

    /**
     * Gets postal_code
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->container['postal_code'];
    }

    /**
     * Sets postal_code
     *
     * @param  string|null  $postal_code  Postal code. Normalized to all capital letters when applicable.
     * @return self
     */
    public function setPostalCode($postal_code)
    {
        if (is_null($postal_code)) {
            throw new \InvalidArgumentException('non-nullable postal_code cannot be null');
        }
        $this->container['postal_code'] = $postal_code;

        return $this;
    }

    /**
     * Gets phone_int_code
     *
     * @return string|null
     */
    public function getPhoneIntCode()
    {
        return $this->container['phone_int_code'];
    }

    /**
     * Sets phone_int_code
     *
     * @param  string|null  $phone_int_code  International dialing code.
     * @return self
     */
    public function setPhoneIntCode($phone_int_code)
    {
        if (is_null($phone_int_code)) {
            throw new \InvalidArgumentException('non-nullable phone_int_code cannot be null');
        }
        $this->container['phone_int_code'] = $phone_int_code;

        return $this;
    }

    /**
     * Gets region
     *
     * @return string|null
     */
    public function getRegion()
    {
        return $this->container['region'];
    }

    /**
     * Sets region
     *
     * @param  string|null  $region  Name of the region, province, or state. Normalized as \"Title Case\".
     * @return self
     */
    public function setRegion($region)
    {
        if (is_null($region)) {
            throw new \InvalidArgumentException('non-nullable region cannot be null');
        }
        $this->container['region'] = $region;

        return $this;
    }

    /**
     * Gets timezone
     *
     * @return string|null
     */
    public function getTimezone()
    {
        return $this->container['timezone'];
    }

    /**
     * Sets timezone
     *
     * @param  string|null  $timezone  Timezone in IANA format.
     * @return self
     */
    public function setTimezone($timezone)
    {
        if (is_null($timezone)) {
            throw new \InvalidArgumentException('non-nullable timezone cannot be null');
        }
        $this->container['timezone'] = $timezone;

        return $this;
    }

    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param  int  $offset  Offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param  int  $offset  Offset
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param  int|null  $offset  Offset
     * @param  mixed  $value  Value to be set
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param  int  $offset  Offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     *               of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}
