<?php declare(strict_types=1);


namespace Freelo;

use Carbon\Carbon;
use Exception;

trait Validations
{
    protected $supportedCurrencies = ['czk', 'eur', 'usd'];

    /**
     * @throws Exception
     */
    public function labelsArrayIsValid(array $labels): bool
    {
        if (empty($labels)) {
            return true;
        }
        $requiredKeys = ['uuid', 'name', 'color'];

        if (count($labels, 1) === count($requiredKeys)) {
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $labels)) {
                    throw new Exception('Invalid labels, check documentation');
                }
            }
        } else {
            foreach ($labels as $item) {
                foreach ($requiredKeys as $key) {
                    if (!array_key_exists($key, $item)) {
                        throw new Exception('Invalid labels, check documentation');
                    }
                }
            }
        }

        return true;
    }

    public function currencyValidation(string $currencyIso): void
    {
        if (!in_array(strtolower($currencyIso), $this->supportedCurrencies)) {
            throw new \InvalidArgumentException('Unsupported currency');
        }
    }

    /**
     * @throws Exception
     */
    public function dueDateGreaterThanStartDate(Carbon $due_date_end, Carbon $due_date): bool
    {
        if ($due_date_end <= $due_date) {
            return false;
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function filesArrayValidation(array $files): void
    {
        $requiredKeys = ['download_url', 'filename'];
        foreach ($files as $file) {
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $file)) {
                    throw new Exception('Invalid key in files array, check documentation');
                }
            }
            $this->validateUrl($file['download_url']);
        }
    }

    /**
     * @throws Exception
     */
    public function validateUrl(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid url provided');
        }

        return $url;
    }

    /**
     * @throws Exception
     */
    public function validateEmailArray(array $emails): bool
    {
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid e-mail provided');
            }
        }
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function validateIdsArrays(array $ids): bool
    {
        foreach ($ids as $id) {
            if (!is_int($id)) {
                throw new Exception('There must bee integers in array');
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function budgetValidation(int $budget): void
    {
        if (floor($budget) !== $budget) {
            throw new Exception(
                'currency amount in string format (2 decimal places with no decimal separator, ie. 1.05 = \'105\')'
            );
        }
    }
}
