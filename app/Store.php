<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    private $_nonceLife;
    private $_nonceSalt;

    public function __construct()
    {
        $this->_nonceLife = env('SHOPIFY_NONCE_LIFE');
        $this->_nonceSalt = env('SHOPIFY_NONCE_SALT');
    }

    public function prepareNonce()
    {
        // Generate Nonce
        $oneTimeNonce = hash(
            'sha256',
            $this->_nonceSalt . (string)microtime() . $this->hostname . $this->_makeRandomString()
        );

        // Get current timestamp
        $currentTime = Carbon::now();

        // Update Store
        $this->nonce = $oneTimeNonce;
        $this->nonce_created_at = $currentTime->toDateTimeString();
        $this->save();

        //return Nonce
        return $this;
    }

    public function clearNonce()
    {
        $this->nonce = null;
        $this->nonce_created_at = null;
        $this->save();

        return $this;
    }

    public function getContactEmails()
    {
        $emails = $this->contact_emails;

        if (empty($emails)) {
            return [];
        }

        $emails = explode(',', $emails);

        $cleanedEmails = [];
        foreach ($emails as $email) {
            $cleanedEmails[] = trim($email);
        }

        return $cleanedEmails;
    }

    private function _makeRandomString($bits = 256)
    {
        $bytes = ceil($bits / 8);
        $return = '';

        for ($i = 0; $i < $bytes; $i++) {
            $return .= chr(mt_rand(0, 255));
        }

        return $return;
    }
}
