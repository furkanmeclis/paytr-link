<?php

namespace FurkanMeclis\PayTRLink;

use FurkanMeclis\PayTRLink\Data\CallbackData;
use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Data\DeleteLinkData;
use FurkanMeclis\PayTRLink\Data\PayTRResponseData;
use FurkanMeclis\PayTRLink\Data\SendEmailData;
use FurkanMeclis\PayTRLink\Data\SendSmsData;
use FurkanMeclis\PayTRLink\Exceptions\PayTRRequestException;
use FurkanMeclis\PayTRLink\Settings\PayTRSettings;
use FurkanMeclis\PayTRLink\Support\PriceConverter;
use FurkanMeclis\PayTRLink\Support\TokenGenerator;
use Illuminate\Support\Facades\Http;

class PayTRLinkService
{
    public function __construct(
        protected ?PayTRSettings $settings = null
    ) {
        // Load settings if available, otherwise use config
        if (! $this->settings) {
            try {
                $this->settings = app(PayTRSettings::class);
            } catch (\Exception $e) {
                // Settings not configured, will use config() directly
                $this->settings = null;
            }
        }
    }

    /**
     * Create a payment link
     */
    public function create(CreateLinkData $data): PayTRResponseData
    {
        $merchantId = $this->getMerchantId();
        $merchantKey = $this->getMerchantKey();
        $merchantSalt = $this->getMerchantSalt();
        $debugOn = $this->getDebugOn();

        // Convert price to kuruş
        $priceInKurus = PriceConverter::toKurus($data->price);

        // Prepare data for token generation
        $tokenData = [
            'name' => $data->name,
            'price' => (string) $priceInKurus,
            'currency' => $data->currency->value,
            'max_installment' => (string) $data->max_installment,
            'link_type' => $data->link_type->value,
            'lang' => $data->lang,
        ];

        if ($data->link_type->value === 'product') {
            $tokenData['min_count'] = (string) ($data->min_count ?? 1);
        } elseif ($data->link_type->value === 'collection') {
            $tokenData['email'] = $data->email ?? '';
        }

        // Generate token
        $paytrToken = TokenGenerator::forCreateLink($tokenData, $merchantKey, $merchantSalt);

        // Prepare request data
        $postData = [
            'merchant_id' => $merchantId,
            'name' => $data->name,
            'price' => $priceInKurus,
            'currency' => $data->currency->value,
            'link_type' => $data->link_type->value,
            'max_installment' => $data->max_installment,
            'lang' => $data->lang,
            'paytr_token' => $paytrToken,
            'debug_on' => $debugOn,
        ];

        // Add optional fields
        if ($data->link_type->value === 'product' && $data->min_count !== null) {
            $postData['min_count'] = $data->min_count;
        }

        if ($data->link_type->value === 'collection' && $data->email !== null) {
            $postData['email'] = $data->email;
        }

        if ($data->expiry_date !== null) {
            $postData['expiry_date'] = $data->expiry_date;
        }

        if ($data->description !== null) {
            $postData['description'] = $data->description;
        }

        $response = $this->request(
            config('paytr-link.api.base_url').config('paytr-link.api.create_link'),
            $postData
        );

        return PayTRResponseData::from($response);
    }

    /**
     * Delete a payment link
     */
    public function delete(DeleteLinkData|string $data): PayTRResponseData
    {
        $linkId = $data instanceof DeleteLinkData ? $data->link_id : $data;
        $merchantId = $this->getMerchantId();
        $merchantKey = $this->getMerchantKey();
        $merchantSalt = $this->getMerchantSalt();
        $debugOn = $this->getDebugOn();

        $paytrToken = TokenGenerator::forDeleteLink($linkId, $merchantId, $merchantKey, $merchantSalt);

        $postData = [
            'merchant_id' => $merchantId,
            'id' => $linkId,
            'paytr_token' => $paytrToken,
            'debug_on' => $debugOn,
        ];

        $response = $this->request(
            config('paytr-link.api.base_url').config('paytr-link.api.delete_link'),
            $postData
        );

        return PayTRResponseData::from($response);
    }

    /**
     * Send SMS for a payment link
     */
    public function sendSms(SendSmsData $data): PayTRResponseData
    {
        $merchantId = $this->getMerchantId();
        $merchantKey = $this->getMerchantKey();
        $merchantSalt = $this->getMerchantSalt();
        $debugOn = $this->getDebugOn();

        $paytrToken = TokenGenerator::forSendSms($data->link_id, $merchantId, $data->phone, $merchantKey, $merchantSalt);

        $postData = [
            'merchant_id' => $merchantId,
            'id' => $data->link_id,
            'cell_phone' => $data->phone,
            'paytr_token' => $paytrToken,
            'debug_on' => $debugOn,
        ];

        $response = $this->request(
            config('paytr-link.api.base_url').config('paytr-link.api.send_sms'),
            $postData
        );

        return PayTRResponseData::from($response);
    }

    /**
     * Send email for a payment link
     */
    public function sendEmail(SendEmailData $data): PayTRResponseData
    {
        $merchantId = $this->getMerchantId();
        $merchantKey = $this->getMerchantKey();
        $merchantSalt = $this->getMerchantSalt();
        $debugOn = $this->getDebugOn();

        $paytrToken = TokenGenerator::forSendEmail($data->link_id, $merchantId, $data->email, $merchantKey, $merchantSalt);

        $postData = [
            'merchant_id' => $merchantId,
            'id' => $data->link_id,
            'email' => $data->email,
            'paytr_token' => $paytrToken,
            'debug_on' => $debugOn,
        ];

        $response = $this->request(
            config('paytr-link.api.base_url').config('paytr-link.api.send_email'),
            $postData
        );

        return PayTRResponseData::from($response);
    }

    /**
     * Validate callback from PayTR
     */
    public function validateCallback(CallbackData|array $data): bool
    {
        $callbackData = $data instanceof CallbackData ? $data : CallbackData::from($data);
        $merchantKey = $this->getMerchantKey();
        $merchantSalt = $this->getMerchantSalt();

        $data = $callbackData->callback_id.$callbackData->merchant_oid.$merchantSalt.
                $callbackData->status.$callbackData->total_amount;
        $expectedHash = base64_encode(hash_hmac('sha256', $data, $merchantKey, true));

        return hash_equals($expectedHash, $callbackData->hash);
    }

    /**
     * Make HTTP request to PayTR API
     */
    protected function request(string $url, array $data): array
    {
        $timeout = config('paytr-link.timeout', 30);

        $response = Http::timeout($timeout)
            ->asForm()
            ->post($url, $data);

        if ($response->failed()) {
            throw new PayTRRequestException(
                'PayTR API Request Failed: '.$response->body(),
                $response->json()
            );
        }

        $responseData = $response->json();

        if (! is_array($responseData)) {
            throw new PayTRRequestException(
                'Invalid response from PayTR API',
                ['raw' => $response->body()]
            );
        }

        return $responseData;
    }

    /**
     * Get merchant ID
     */
    protected function getMerchantId(): string
    {
        return $this->settings?->getMerchantId() ?? config('paytr-link.merchant_id', '');
    }

    /**
     * Get merchant key
     */
    protected function getMerchantKey(): string
    {
        return $this->settings?->getMerchantKey() ?? config('paytr-link.merchant_key', '');
    }

    /**
     * Get merchant salt
     */
    protected function getMerchantSalt(): string
    {
        return $this->settings?->getMerchantSalt() ?? config('paytr-link.merchant_salt', '');
    }

    /**
     * Get debug mode
     */
    protected function getDebugOn(): int
    {
        return $this->settings?->getDebugOn() ?? (config('paytr-link.debug_on', 1) ? 1 : 0);
    }
}
