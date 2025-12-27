<?php

namespace FurkanMeclis\PayTRLink\Support;

class TokenGenerator
{
    /**
     * Generate HMAC-SHA256 token for PayTR API
     */
    public static function generate(string $data, string $key, string $salt): string
    {
        return base64_encode(hash_hmac('sha256', $data.$salt, $key, true));
    }

    /**
     * Generate token for link creation
     */
    public static function forCreateLink(array $data, string $key, string $salt): string
    {
        $requiredString = $data['name'].$data['price'].$data['currency'].
                         $data['max_installment'].$data['link_type'].$data['lang'];

        if ($data['link_type'] === 'product') {
            $requiredString .= $data['min_count'] ?? '1';
        } elseif ($data['link_type'] === 'collection') {
            $requiredString .= $data['email'] ?? '';
        }

        return self::generate($requiredString, $key, $salt);
    }

    /**
     * Generate token for link deletion
     */
    public static function forDeleteLink(string $linkId, string $merchantId, string $key, string $salt): string
    {
        return self::generate($linkId.$merchantId, $key, $salt);
    }

    /**
     * Generate token for SMS sending
     */
    public static function forSendSms(string $linkId, string $merchantId, string $phone, string $key, string $salt): string
    {
        return self::generate($linkId.$merchantId.$phone, $key, $salt);
    }

    /**
     * Generate token for email sending
     */
    public static function forSendEmail(string $linkId, string $merchantId, string $email, string $key, string $salt): string
    {
        return self::generate($linkId.$merchantId.$email, $key, $salt);
    }

    /**
     * Generate token for callback validation
     */
    public static function forCallback(string $callbackId, string $merchantOid, string $salt, string $status, string $totalAmount, string $key): string
    {
        $data = $callbackId.$merchantOid.$salt.$status.$totalAmount;

        return base64_encode(hash_hmac('sha256', $data, $key, true));
    }
}
