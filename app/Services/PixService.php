<?php

namespace App\Services;

class PixService
{
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_POINT_OF_INITIATION_METHOD = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_CRC16 = '63';

    /**
     * Generate the PIX Copy and Paste code
     *
     * @param string $pixKey
     * @param string $pixKeyType (CPF, CNPJ, PHONE, EMAIL, RANDOM)
     * @param string $description
     * @param string $merchantName
     * @param string $merchantCity
     * @param string $amount
     * @param string $txid
     * @return string
     */
    public function generatePayload($pixKey, $pixKeyType, $merchantName, $merchantCity, $amount = null, $txid = '***')
    {
        // Format PIX Key based on type
        $pixKey = $this->formatPixKey($pixKey, $pixKeyType);
        $merchantName = $this->formatText($merchantName, 25);
        $merchantCity = $this->formatText($merchantCity, 15);
        $amount = $amount ? number_format((float)$amount, 2, '.', '') : null;

        // Merchant Account Information (GUI + Key + Description)
        $gui = '0014br.gov.bcb.pix';
        $key = '01' . sprintf('%02d', strlen($pixKey)) . $pixKey;
        // Optional: Payment Description (02) could be added here if needed
        $merchantAccountInfo = $gui . $key;

        // Build Payload
        $payload =
            $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR, '01') .
            $this->getValue(self::ID_POINT_OF_INITIATION_METHOD, '12') . // 12 = Dynamic (since we might have amount), 11 = Static
            $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION, $merchantAccountInfo) .
            $this->getValue(self::ID_MERCHANT_CATEGORY_CODE, '0000') . // 0000 = Not specified / General
            $this->getValue(self::ID_TRANSACTION_CURRENCY, '986') . // BRL
            ($amount ? $this->getValue(self::ID_TRANSACTION_AMOUNT, $amount) : '') .
            $this->getValue(self::ID_COUNTRY_CODE, 'BR') .
            $this->getValue(self::ID_MERCHANT_NAME, $merchantName) .
            $this->getValue(self::ID_MERCHANT_CITY, $merchantCity) .
            $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $this->getValue('05', $txid)); // 05 = Reference Label (TXID)

        // Add CRC16 ID
        $payload .= self::ID_CRC16 . '04';

        // Calculate CRC16
        $crc16 = $this->getCRC16($payload);

        return $payload . $crc16;
    }

    private function getValue($id, $value)
    {
        $size =  sprintf('%02d', strlen($value));
        return $id . $size . $value;
    }

    private function formatPixKey($key, $type)
    {
        // Remove non-alphanumeric chars for specific types if needed, but email needs @, random needs dashes etc.
        // For CPF/CNPJ/PHONE usually we strip non-digits.
        // Email and Random key should be kept as is generally.

        switch (strtoupper($type)) {
            case 'CPF':
            case 'CNPJ':
            case 'PHONE':
                return preg_replace('/[^0-9]/', '', $key);
            case 'EMAIL':
            case 'RANDOM':
            default:
                return $key;
        }
    }

    private function formatText($text, $limit)
    {
        // Replace accents
        $replacements = [
            'á' => 'a',
            'à' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'õ' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ç' => 'c',
            'ñ' => 'n',
            'Á' => 'A',
            'À' => 'A',
            'Ã' => 'A',
            'Â' => 'A',
            'Ä' => 'A',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ó' => 'O',
            'Ò' => 'O',
            'Õ' => 'O',
            'Ô' => 'O',
            'Ö' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ç' => 'C',
            'Ñ' => 'N'
        ];

        $text = strtr($text, $replacements);

        // Keep only supported characters (alphanumeric and spaces) - keeping it simple
        // EMV standard supports more but we want to be safe
        // $text = preg_replace('/[^a-zA-Z0-9 ]/', '', $text); 

        return substr(strtoupper($text), 0, $limit);
    }

    private function getCRC16($payload)
    {
        $polynomial = 0x1021;
        $resultado = 0xFFFF;

        if (strlen($payload) > 0) {
            for ($offset = 0; $offset < strlen($payload); $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polynomial;
                    $resultado &= 0xFFFF;
                }
            }
        }

        return strtoupper(sprintf('%04x', $resultado));
    }
}
