<?php
namespace common\base;

use Yii;

class PasswordHash {

    const ALGORITHM = PASSWORD_BCRYPT;

    // 2^12 iterations
    const COST = 12;

    /**
     * Create password hash
     *
     * @param string $password Password
     * @return string
     */
    public function hash($password) {
        $options = [
            'cost' => self::COST
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    /**
     * Verify password
     *
     * @param string $password Password
     * @param string $hash Hash
     * @return boolean
     */
    public function verify($password, $hash) {
        return password_verify($password, $hash);
    }

}